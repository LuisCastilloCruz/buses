@extends('tenant.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <section class="card card-modern card-big-info">
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <h2 class="font-weight-bold text-6 mb-1">Tattoo Dashboard</h2>
                        <p class="text-muted mb-0">Resumen general del estudio de tatuaje</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <section class="card card-featured-left card-featured-primary h-100 mb-0">
                            <div class="card-body">
                                <div class="text-muted small">Citas de hoy</div>
                                <div class="text-8 font-weight-bold">{{ $todayAppointments }}</div>
                                <div class="small mt-2 {{ $appointmentsDiff >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $appointmentsDiff >= 0 ? '+' : '' }}{{ $appointmentsDiff }} vs ayer
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <section class="card card-featured-left card-featured-warning h-100 mb-0">
                            <div class="card-body">
                                <div class="text-muted small">Sesiones pendientes</div>
                                <div class="text-8 font-weight-bold">{{ $pendingSessions }}</div>
                                <div class="text-warning small mt-2">
                                    {{ $sessionsNeedConfirmation }} requieren confirmación
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <section class="card card-featured-left card-featured-success h-100 mb-0">
                            <div class="card-body">
                                <div class="text-muted small">Ingresos del mes</div>
                                <div class="text-8 font-weight-bold">S/ {{ number_format((float) $monthlyIncome, 2) }}</div>
                                <div class="text-success small mt-2">{{ $incomeVariationText }}</div>
                            </div>
                        </section>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <section class="card card-featured-left card-featured-danger h-100 mb-0">
                            <div class="card-body">
                                <div class="text-muted small">Consentimientos pendientes</div>
                                <div class="text-8 font-weight-bold">{{ $pendingConsents }}</div>
                                <div class="text-danger small mt-2">Revisar antes de tatuar</div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <section class="card mb-0">
                            <header class="card-header d-flex justify-content-between align-items-center">
                                <h2 class="card-title mb-0">Próximas citas</h2>
                                <a href="{{ route('tenant.tattoo-appointments.index') }}" class="btn btn-sm btn-primary">
                                    Ver agenda
                                </a>
                            </header>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Hora</th>
                                                <th>Cliente</th>
                                                <th>Artista</th>
                                                <th>Zona</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($upcomingAppointments as $appointment)
                                                <tr>
                                                    <td>{{ $appointment->appointment_date ?: '-' }}</td>
                                                    <td>{{ $appointment->appointment_time ?: '-' }}</td>
                                                    <td>{{ optional($appointment->person)->name ?: '-' }}</td>
                                                    <td>{{ optional($appointment->artist)->stage_name ?: '-' }}</td>
                                                    <td>{{ $appointment->body_zone ?: '-' }}</td>
                                                    <td>
                                                        @if($appointment->status === 'pending')
                                                            <span class="badge badge-warning">Pendiente</span>
                                                        @elseif($appointment->status === 'confirmed')
                                                            <span class="badge badge-primary">Confirmada</span>
                                                        @elseif($appointment->status === 'in_progress')
                                                            <span class="badge badge-success">En proceso</span>
                                                        @elseif($appointment->status === 'completed')
                                                            <span class="badge badge-secondary">Completada</span>
                                                        @elseif($appointment->status === 'cancelled')
                                                            <span class="badge badge-danger">Cancelada</span>
                                                        @else
                                                            <span class="badge badge-light">{{ $appointment->status ?: 'Sin estado' }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        No hay próximas citas registradas.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <section class="card mb-4">
                            <header class="card-header">
                                <h2 class="card-title">Top artistas</h2>
                            </header>
                            <div class="card-body">
                                @forelse($topArtists as $artist)
                                    <div class="mb-3">
                                        <strong>{{ $artist->stage_name ?: 'Sin nombre' }}</strong>
                                        <div class="small text-muted">
                                            {{ $artist->appointments_count }} citas registradas
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">
                                        No hay artistas con citas registradas.
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        <section class="card mb-0">
                            <header class="card-header">
                                <h2 class="card-title">Accesos rápidos</h2>
                            </header>
                            <div class="card-body">
                                <a href="{{ route('tenant.tattoo-appointments.index') }}" class="btn btn-outline-primary btn-block mb-2">
                                    Nueva cita
                                </a>
                                <a href="{{ route('tenant.tattoo-sessions.index') }}" class="btn btn-outline-dark btn-block mb-2">
                                    Registrar sesión
                                </a>
                                <a href="{{ route('tenant.tattoo-consents.index') }}" class="btn btn-outline-success btn-block">
                                    Ver consentimientos
                                </a>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>
@endsection