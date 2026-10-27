@extends('layouts.app')

@section('title', 'Agendar cita | MediCitas')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-8">
            <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-teal-700">Nueva consulta</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Agendar una cita</h1>
            <p class="mt-2 text-slate-600">Completa los datos para registrar una cita médica.</p>
        </div>

        <form method="POST" action="{{ route('citas.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @csrf

            <div>
                <label for="patient_id" class="mb-1.5 block text-sm font-medium text-slate-700">Paciente</label>
                <select id="patient_id" name="patient_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20">
                    <option value="">Selecciona un paciente</option>
                    @foreach ($pacientes as $paciente)
                        <option value="{{ $paciente->id }}" @selected(old('patient_id') == $paciente->id)>{{ $paciente->name }}</option>
                    @endforeach
                </select>
                @error('patient_id') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="doctor_id" class="mb-1.5 block text-sm font-medium text-slate-700">Médico</label>
                <select id="doctor_id" name="doctor_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20">
                    <option value="">Selecciona un médico</option>
                    @foreach ($medicos as $medico)
                        <option value="{{ $medico->id }}" @selected(old('doctor_id') == $medico->id)>{{ $medico->name }}</option>
                    @endforeach
                </select>
                @error('doctor_id') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="especialidad" class="mb-1.5 block text-sm font-medium text-slate-700">Especialidad</label>
                    <input id="especialidad" name="especialidad" type="text" value="{{ old('especialidad') }}" required maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20">
                    @error('especialidad') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="fecha_hora" class="mb-1.5 block text-sm font-medium text-slate-700">Fecha y hora</label>
                    <input id="fecha_hora" name="fecha_hora" type="datetime-local" value="{{ old('fecha_hora') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20">
                    @error('fecha_hora') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="motivo" class="mb-1.5 block text-sm font-medium text-slate-700">Motivo de la consulta</label>
                <textarea id="motivo" name="motivo" rows="4" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20">{{ old('motivo') }}</textarea>
                @error('motivo') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('citas.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">Guardar cita</button>
            </div>
        </form>
    </div>
@endsection
