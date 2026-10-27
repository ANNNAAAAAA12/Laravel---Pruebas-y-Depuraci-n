<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\User;
use App\Notifications\CitaAgendadaNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CitaController extends Controller
{
    /**
     * RF 2 & RT 2: Listar citas médicas en HTML o JSON.
     * Carga ansiosa (with) de relaciones para prevenir el problema N+1.
     */
    public function index(Request $request): JsonResponse|View
    {
        $query = Cita::with(['patient', 'doctor']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $citas = $query->get();

        if ($request->wantsJson()) {
            return response()->json($citas);
        }

        return view('citas.index', compact('citas'));
    }

    /**
     * RF 2: Vista del formulario de agendamiento.
     */
    public function create(): View
    {
        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);

        return view('citas.create', [
            'medicos' => $usuarios,
            'pacientes' => $usuarios,
        ]);
    }

    /**
     * RF 2 & RT 2: Registrar una nueva cita y notificar al médico por correo.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => [
                Rule::requiredIf($request->user() === null),
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'doctor_id' => 'required|exists:users,id',
            'especialidad' => 'required|string|max:255',
            'fecha_hora' => 'required|date',
            'motivo' => 'required|string|max:5000',
        ]);

        $cita = Cita::create([
            'patient_id' => $request->user()?->getAuthIdentifier() ?? $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'especialidad' => $validated['especialidad'],
            'fecha_hora' => $validated['fecha_hora'],
            'estado' => 'pendiente',
            'motivo' => $validated['motivo'],
        ]);

        $cita->load(['patient', 'doctor']);
        $cita->doctor->notify(new CitaAgendadaNotification($cita));

        if ($request->wantsJson()) {
            return response()->json($cita, 201);
        }

        return redirect()->route('citas.index')->with('success', 'Cita agendada correctamente.');
    }

    public function updateStatus(Request $request, Cita $cita): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', Rule::in(['confirmada', 'cancelada'])],
        ]);

        $updated = Cita::query()
            ->whereKey($cita->getKey())
            ->where('estado', 'pendiente')
            ->update([
                'estado' => $validated['estado'],
            ]);

        if ($updated === 0) {
            $message = 'Solo se pueden confirmar o cancelar citas que todavía están pendientes.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 409);
            }

            return redirect()->route('citas.index')->with('error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json($cita->fresh()->load(['patient', 'doctor']));
        }

        $estado = $validated['estado'] === 'confirmada' ? 'confirmada' : 'cancelada';

        return redirect()->route('citas.index')->with('success', "La cita quedó {$estado} correctamente.");
    }
}
