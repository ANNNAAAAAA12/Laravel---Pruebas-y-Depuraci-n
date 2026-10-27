@extends('layouts.app')

@section('title', 'Citas médicas | MediCitas')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-teal-700">Agenda médica</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Citas agendadas</h1>
            <p class="mt-2 text-slate-600">Consulta y filtra las citas registradas en la clínica.</p>
        </div>
        <a href="{{ route('citas.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">
            Agendar una cita
        </a>
    </div>

    <form method="GET" action="{{ route('citas.index') }}" class="mb-6 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="estado" class="mb-1.5 block text-sm font-medium text-slate-700">Filtrar por estado</label>
            <select id="estado" name="estado" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20">
                <option value="">Todos los estados</option>
                @foreach (['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Aplicar filtro</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-4 font-semibold">Fecha y hora</th>
                        <th scope="col" class="px-5 py-4 font-semibold">Paciente</th>
                        <th scope="col" class="px-5 py-4 font-semibold">Médico</th>
                        <th scope="col" class="px-5 py-4 font-semibold">Especialidad</th>
                        <th scope="col" class="px-5 py-4 font-semibold">Estado</th>
                        <th scope="col" class="px-5 py-4 font-semibold">Motivo</th>
                        <th scope="col" class="px-5 py-4 font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($citas as $cita)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-800">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $cita->patient->name }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $cita->doctor->name }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $cita->especialidad }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-amber-100 text-amber-800' => $cita->estado === 'pendiente',
                                    'bg-emerald-100 text-emerald-800' => $cita->estado === 'confirmada',
                                    'bg-slate-100 text-slate-700' => $cita->estado === 'cancelada',
                                ])>{{ ucfirst($cita->estado) }}</span>
                            </td>
                            <td class="max-w-xs px-5 py-4 text-slate-600">{{ $cita->motivo }}</td>
                            <td class="px-5 py-4">
                                @if ($cita->estado === 'pendiente')
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('citas.estado', $cita) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="confirmada">
                                            <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800">
                                                Aprobar
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('citas.estado', $cita) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="cancelada">
                                            <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                                Cancelar
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500">Sin acciones disponibles</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="font-semibold text-slate-800">No hay citas para mostrar</p>
                                <p class="mt-1 text-sm text-slate-500">Prueba otro filtro o agenda una nueva cita.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
