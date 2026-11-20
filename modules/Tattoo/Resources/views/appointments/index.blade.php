@extends('tenant.layouts.app')

@section('content')
@php
    function tattooAppointmentStatusBadge($status) {
        switch ($status) {
            case 'pending':
                return '<span class="badge badge-warning">Pendiente</span>';
            case 'confirmed':
                return '<span class="badge badge-primary">Confirmada</span>';
            case 'in_progress':
                return '<span class="badge badge-success">En proceso</span>';
            case 'completed':
                return '<span class="badge badge-secondary">Completada</span>';
            case 'cancelled':
                return '<span class="badge badge-danger">Cancelada</span>';
            default:
                return '<span class="badge badge-light">'.e($status ?: 'Sin estado').'</span>';
        }
    }
@endphp

<div class="row">
    <div class="col-12">
        <section class="card shadow-sm border-0">
            <header class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center border-bottom">
                <div class="mb-2 mb-md-0">
                    <h2 class="card-title mb-1">Agenda Tattoo</h2>
                    <p class="mb-0 text-muted">Gestión de citas, control de agenda y seguimiento del proyecto del cliente</p>
                </div>

                <div class="d-flex flex-wrap">
                    <button class="btn btn-primary mr-2 mb-2 mb-md-0" id="btnNewAppointment" type="button">
                        <i class="fa fa-plus mr-1"></i> Nueva cita
                    </button>
                </div>
            </header>

            <div class="card-body border-bottom">
                <form method="GET" class="row">
                    <div class="col-md-3 form-group">
                        <label>Fecha</label>
                        <input
                            type="date"
                            name="appointment_date"
                            class="form-control"
                            value="{{ request('appointment_date') }}"
                        >
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Artista</label>
                        <select name="artist_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($artists as $artist)
                                <option value="{{ $artist->id }}" {{ (string) request('artist_id') === (string) $artist->id ? 'selected' : '' }}>
                                    {{ $artist->stage_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En proceso</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completada</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Cliente</label>
                        <input
                            type="text"
                            name="customer_name"
                            class="form-control"
                            value="{{ request('customer_name') }}"
                            placeholder="Buscar cliente"
                        >
                    </div>

                    <div class="col-md-12 form-group d-flex flex-wrap align-items-end">
                        <button class="btn btn-default mr-2 mb-2 mb-md-0" type="submit">
                            <i class="fa fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('tenant.tattoo-appointments.index') }}" class="btn btn-light border mb-2 mb-md-0">
                            <i class="fa fa-eraser mr-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body bg-light border-bottom">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Total citas visibles</div>
                                <div class="h3 mb-0">{{ $appointments->total() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Citas con consentimiento</div>
                                <div class="h3 mb-0">
                                    {{ $appointments->getCollection()->filter(function($item){ return !is_null($item->consent); })->count() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Citas con sesiones</div>
                                <div class="h3 mb-0">
                                    {{ $appointments->getCollection()->filter(function($item){ return (int) $item->sessions_count > 0; })->count() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Monto estimado visible</div>
                                <div class="h5 mb-0">
                                    S/
                                    {{ number_format((float) $appointments->getCollection()->sum('estimated_price'), 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0" id="appointmentsTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="70">ID</th>
                                <th width="110">Fecha</th>
                                <th width="90">Hora</th>
                                <th>Cliente</th>
                                <th>Artista</th>
                                <th>Estilo</th>
                                <th>Zona</th>
                                <th width="120">Estado</th>
                                <th width="120">Consentimiento</th>
                                <th width="100">Sesiones</th>
                                <th width="120">Adelanto</th>
                                <th width="120">Estimado</th>
                                <th width="260">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                @php
                                    $hasConsent = !is_null($appointment->consent);
                                    $consentAccepted = $hasConsent && (int) $appointment->consent->accepted === 1;
                                @endphp
                                <tr id="appointment-row-{{ $appointment->id }}">
                                    <td>#{{ $appointment->id }}</td>
                                    <td>{{ $appointment->appointment_date ?: '-' }}</td>
                                    <td>{{ $appointment->appointment_time ?: '-' }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ optional($appointment->person)->name ?: '-' }}</div>
                                        @if(optional($appointment->person)->number)
                                            <small class="text-muted">{{ optional($appointment->person)->number }}</small>
                                        @endif
                                    </td>
                                    <td>{{ optional($appointment->artist)->stage_name ?: '-' }}</td>
                                    <td>{{ optional($appointment->style)->name ?: '-' }}</td>
                                    <td>{{ $appointment->body_zone ?: '-' }}</td>
                                    <td>{!! tattooAppointmentStatusBadge($appointment->status) !!}</td>
                                    <td>
                                        @if($hasConsent)
                                            @if($consentAccepted)
                                                <span class="badge badge-success">Aceptado</span>
                                            @else
                                                <span class="badge badge-warning">Registrado</span>
                                            @endif
                                        @else
                                            <span class="badge badge-light">Sin consentimiento</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if((int) $appointment->sessions_count > 0)
                                            <span class="badge badge-info">{{ (int) $appointment->sessions_count }}</span>
                                        @else
                                            <span class="badge badge-light">0</span>
                                        @endif
                                    </td>
                                    <td>S/ {{ number_format((float) $appointment->deposit_amount, 2) }}</td>
                                    <td>S/ {{ number_format((float) $appointment->estimated_price, 2) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm d-flex flex-wrap" role="group">
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-view-appointment mb-1"
                                                data-id="{{ $appointment->id }}">
                                                Ver
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-info btn-edit-appointment mb-1"
                                                data-id="{{ $appointment->id }}">
                                                Editar
                                            </button>

                                            <a
                                                href="{{ url('tattoo-sessions') }}?tattoo_appointment_id={{ $appointment->id }}"
                                                class="btn btn-outline-primary mb-1">
                                                Sesiones
                                            </a>

                                            <a
                                                href="{{ url('tattoo-consents') }}?tattoo_appointment_id={{ $appointment->id }}"
                                                class="btn btn-outline-dark mb-1">
                                                Consent.
                                            </a>

                                            <button
                                                type="button"
                                                class="btn btn-danger btn-delete-appointment mb-1"
                                                data-id="{{ $appointment->id }}"
                                                data-name="{{ optional($appointment->person)->name ?: 'la cita' }}">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="row-empty-appointments">
                                    <td colspan="13" class="text-center text-muted py-4">
                                        No hay citas registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($appointments, 'links'))
                <div class="card-footer bg-white">
                    {{ $appointments->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

<div class="modal fade" id="modalAppointment" tabindex="-1" role="dialog" aria-labelledby="modalAppointmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="appointmentForm">
            @csrf
            <input type="hidden" name="id" id="appointment_id">

            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white">
                    <h5 class="modal-title" id="modalAppointmentLabel">Nueva cita de tatuaje</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="appointmentFormAlert" class="alert alert-danger d-none"></div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Cliente <span class="text-danger">*</span></label>
                            <select class="form-control" name="person_id" id="person_id" required>
                                <option value="">Seleccionar cliente</option>
                                @foreach($persons as $person)
                                    <option value="{{ $person->id }}">
                                        {{ $person->name }} {{ $person->number ? '- '.$person->number : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Artista <span class="text-danger">*</span></label>
                            <select class="form-control" name="tattoo_artist_id" id="tattoo_artist_id" required>
                                <option value="">Seleccionar artista</option>
                                @foreach($artists as $artist)
                                    <option value="{{ $artist->id }}">
                                        {{ $artist->stage_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Estilo</label>
                            <select class="form-control" name="tattoo_style_id" id="tattoo_style_id">
                                <option value="">Seleccionar estilo</option>
                                @foreach($styles as $style)
                                    <option value="{{ $style->id }}">
                                        {{ $style->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Zona del cuerpo</label>
                            <input
                                type="text"
                                class="form-control"
                                name="body_zone"
                                id="body_zone"
                                placeholder="Ej. brazo, antebrazo, espalda"
                            >
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Tamaño</label>
                            <input
                                type="text"
                                class="form-control"
                                name="tattoo_size"
                                id="tattoo_size"
                                placeholder="Ej. pequeño, mediano, 10 cm"
                            >
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Fecha <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="appointment_date" id="appointment_date" required>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Hora</label>
                            <input type="time" class="form-control" name="appointment_time" id="appointment_time">
                            <small class="text-muted">Se valida cruce con otras citas y sesiones del artista.</small>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Precio estimado</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control"
                                name="estimated_price"
                                id="estimated_price"
                                placeholder="0.00"
                            >
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Adelanto</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control"
                                name="deposit_amount"
                                id="deposit_amount"
                                placeholder="0.00"
                            >
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Estado</label>
                            <select class="form-control" name="status" id="status">
                                <option value="pending">Pendiente</option>
                                <option value="confirmed">Confirmada</option>
                                <option value="in_progress">En proceso</option>
                                <option value="completed">Completada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Idea del tatuaje</label>
                            <textarea
                                class="form-control"
                                rows="3"
                                name="idea"
                                id="idea"
                                placeholder="Describe la idea, referencia, concepto, símbolos, estilo visual o inspiración"
                            ></textarea>
                        </div>

                        <div class="col-md-12 form-group mb-0">
                            <label>Observaciones</label>
                            <textarea class="form-control" rows="2" name="observations" id="observations"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white">
                    <button class="btn btn-light border" type="button" data-dismiss="modal">Cerrar</button>
                    <button class="btn btn-success" type="submit" id="btnSaveAppointment">Guardar cita</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAppointmentDetail" tabindex="-1" role="dialog" aria-labelledby="modalAppointmentDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white">
                <h5 class="modal-title" id="modalAppointmentDetailLabel">Detalle de la cita</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="appointmentDetailLoader" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Cargando información...</div>
                </div>

                <div id="appointmentDetailContent" class="d-none">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-3">Datos principales</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Cliente</th>
                                        <td id="detail_person_name">-</td>
                                    </tr>
                                    <tr>
                                        <th>Artista</th>
                                        <td id="detail_artist_name">-</td>
                                    </tr>
                                    <tr>
                                        <th>Estilo</th>
                                        <td id="detail_style_name">-</td>
                                    </tr>
                                    <tr>
                                        <th>Fecha</th>
                                        <td id="detail_appointment_date">-</td>
                                    </tr>
                                    <tr>
                                        <th>Hora</th>
                                        <td id="detail_appointment_time">-</td>
                                    </tr>
                                    <tr>
                                        <th>Estado</th>
                                        <td id="detail_status">-</td>
                                    </tr>
                                    <tr>
                                        <th>Zona</th>
                                        <td id="detail_body_zone">-</td>
                                    </tr>
                                    <tr>
                                        <th>Tamaño</th>
                                        <td id="detail_tattoo_size">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-3">Resumen comercial</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="55%">Precio estimado</th>
                                        <td id="detail_estimated_price">-</td>
                                    </tr>
                                    <tr>
                                        <th>Adelanto</th>
                                        <td id="detail_deposit_amount">-</td>
                                    </tr>
                                    <tr>
                                        <th>Total sesiones</th>
                                        <td id="detail_session_total">-</td>
                                    </tr>
                                    <tr>
                                        <th>Total pagado en sesiones</th>
                                        <td id="detail_session_paid">-</td>
                                    </tr>
                                    <tr>
                                        <th>Saldo pendiente</th>
                                        <td id="detail_pending_balance" class="font-weight-bold">-</td>
                                    </tr>
                                    <tr>
                                        <th>Consentimiento</th>
                                        <td id="detail_consent_status">-</td>
                                    </tr>
                                    <tr>
                                        <th>Fecha firma</th>
                                        <td id="detail_consent_signed_date">-</td>
                                    </tr>
                                    <tr>
                                        <th>N° sesiones</th>
                                        <td id="detail_sessions_count">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Idea del tatuaje</h6>
                                <div id="detail_idea" class="text-dark" style="white-space: pre-wrap;">-</div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Observaciones</h6>
                                <div id="detail_observations" class="text-dark" style="white-space: pre-wrap;">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="appointmentDetailEmpty" class="alert alert-danger d-none mb-0">
                    No se pudo cargar el detalle de la cita.
                </div>
            </div>

            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnEditFromDetail">
                    Editar cita
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    $(function () {
        var modal = $('#modalAppointment');
        var form = $('#appointmentForm');
        var alertBox = $('#appointmentFormAlert');
        var saveBtn = $('#btnSaveAppointment');

        var detailModal = $('#modalAppointmentDetail');
        var detailLoader = $('#appointmentDetailLoader');
        var detailContent = $('#appointmentDetailContent');
        var detailEmpty = $('#appointmentDetailEmpty');
        var currentDetailId = null;

        function money(value) {
            var number = parseFloat(value || 0);
            return 'S/ ' + number.toFixed(2);
        }

        function statusBadge(status) {
            switch (status) {
                case 'pending':
                    return '<span class="badge badge-warning">Pendiente</span>';
                case 'confirmed':
                    return '<span class="badge badge-primary">Confirmada</span>';
                case 'in_progress':
                    return '<span class="badge badge-success">En proceso</span>';
                case 'completed':
                    return '<span class="badge badge-secondary">Completada</span>';
                case 'cancelled':
                    return '<span class="badge badge-danger">Cancelada</span>';
                default:
                    return '<span class="badge badge-light">' + (status || 'Sin estado') + '</span>';
            }
        }

        function resetForm() {
            if (form.length && form[0]) {
                form[0].reset();
            }

            $('#appointment_id').val('');
            $('#modalAppointmentLabel').text('Nueva cita de tatuaje');
            $('#status').val('pending');
            alertBox.addClass('d-none').html('');
        }

        function showError(message) {
            alertBox.removeClass('d-none').html(message);
        }

        function setLoading(isLoading) {
            saveBtn.prop('disabled', isLoading);
            saveBtn.text(isLoading ? 'Guardando...' : 'Guardar cita');
        }

        function openEditModal(data) {
            resetForm();
            $('#modalAppointmentLabel').text('Editar cita');

            $('#appointment_id').val(data.id || '');
            $('#person_id').val(data.person_id || '');
            $('#tattoo_artist_id').val(data.tattoo_artist_id || '');
            $('#tattoo_style_id').val(data.tattoo_style_id || '');
            $('#appointment_date').val(data.appointment_date || '');
            $('#appointment_time').val(data.appointment_time || '');
            $('#status').val(data.status || 'pending');
            $('#body_zone').val(data.body_zone || '');
            $('#tattoo_size').val(data.tattoo_size || '');
            $('#estimated_price').val(data.estimated_price || '');
            $('#deposit_amount').val(data.deposit_amount || '');
            $('#idea').val(data.idea || '');
            $('#observations').val(data.observations || '');

            modal.modal('show');
        }

        function resetDetailModal() {
            currentDetailId = null;
            detailLoader.addClass('d-none');
            detailContent.addClass('d-none');
            detailEmpty.addClass('d-none');

            $('#detail_person_name').text('-');
            $('#detail_artist_name').text('-');
            $('#detail_style_name').text('-');
            $('#detail_appointment_date').text('-');
            $('#detail_appointment_time').text('-');
            $('#detail_status').html('-');
            $('#detail_body_zone').text('-');
            $('#detail_tattoo_size').text('-');
            $('#detail_estimated_price').text('-');
            $('#detail_deposit_amount').text('-');
            $('#detail_session_total').text('-');
            $('#detail_session_paid').text('-');
            $('#detail_pending_balance').text('-');
            $('#detail_consent_status').html('-');
            $('#detail_consent_signed_date').text('-');
            $('#detail_sessions_count').text('-');
            $('#detail_idea').text('-');
            $('#detail_observations').text('-');
        }

        function fillDetailModal(data) {
            $('#detail_person_name').text(data.person_name || '-');
            $('#detail_artist_name').text(data.artist_name || '-');
            $('#detail_style_name').text(data.style_name || '-');
            $('#detail_appointment_date').text(data.appointment_date || '-');
            $('#detail_appointment_time').text(data.appointment_time || '-');
            $('#detail_status').html(statusBadge(data.status));
            $('#detail_body_zone').text(data.body_zone || '-');
            $('#detail_tattoo_size').text(data.tattoo_size || '-');
            $('#detail_estimated_price').text(money(data.estimated_price));
            $('#detail_deposit_amount').text(money(data.deposit_amount));
            $('#detail_session_total').text(money(data.session_total));
            $('#detail_session_paid').text(money(data.session_paid));
            $('#detail_pending_balance').text(money(data.pending_balance));
            $('#detail_sessions_count').text(data.sessions_count || 0);
            $('#detail_idea').text(data.idea || '-');
            $('#detail_observations').text(data.observations || '-');

            if (data.has_consent) {
                if (parseInt(data.consent_accepted, 10) === 1) {
                    $('#detail_consent_status').html('<span class="badge badge-success">Consentimiento aceptado</span>');
                } else {
                    $('#detail_consent_status').html('<span class="badge badge-warning">Consentimiento registrado</span>');
                }
            } else {
                $('#detail_consent_status').html('<span class="badge badge-light">Sin consentimiento</span>');
            }

            $('#detail_consent_signed_date').text(data.consent_signed_date || '-');
        }

        $('#btnNewAppointment').on('click', function () {
            resetForm();
            modal.modal('show');
        });

        $(document).on('click', '.btn-edit-appointment', function () {
            var id = $(this).data('id');
            resetForm();
            $('#modalAppointmentLabel').text('Editar cita');

            $.ajax({
                url: "{{ url('tattoo-appointments/record') }}/" + id,
                type: "GET",
                success: function (response) {
                    openEditModal(response);
                },
                error: function () {
                    alert('No se pudo cargar la cita.');
                }
            });
        });

        $(document).on('click', '.btn-view-appointment', function () {
            var id = $(this).data('id');

            resetDetailModal();
            currentDetailId = id;
            detailModal.modal('show');
            detailLoader.removeClass('d-none');

            $.ajax({
                url: "{{ url('tattoo-appointments/record') }}/" + id,
                type: "GET",
                success: function (response) {
                    detailLoader.addClass('d-none');
                    detailContent.removeClass('d-none');
                    fillDetailModal(response);
                },
                error: function () {
                    detailLoader.addClass('d-none');
                    detailEmpty.removeClass('d-none');
                }
            });
        });

        $('#btnEditFromDetail').on('click', function () {
            if (!currentDetailId) {
                return;
            }

            $.ajax({
                url: "{{ url('tattoo-appointments/record') }}/" + currentDetailId,
                type: "GET",
                success: function (response) {
                    detailModal.modal('hide');
                    openEditModal(response);
                },
                error: function () {
                    alert('No se pudo cargar la cita para edición.');
                }
            });
        });

        form.on('submit', function (e) {
            e.preventDefault();
            alertBox.addClass('d-none').html('');
            setLoading(true);

            $.ajax({
                url: "{{ route('tenant.tattoo-appointments.index') }}",
                type: "POST",
                data: form.serialize(),
                success: function (response) {
                    if (response.success) {
                        modal.modal('hide');
                        window.location.reload();
                    } else {
                        showError(response.message || 'No se pudo guardar la cita.');
                    }
                },
                error: function (xhr) {
                    var message = 'No se pudo guardar la cita.';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        if (xhr.responseJSON.errors) {
                            var errors = [];
                            Object.keys(xhr.responseJSON.errors).forEach(function (key) {
                                errors = errors.concat(xhr.responseJSON.errors[key]);
                            });
                            message = errors.join('<br>');
                        }
                    }

                    showError(message);
                },
                complete: function () {
                    setLoading(false);
                }
            });
        });

        $(document).on('click', '.btn-delete-appointment', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');

            if (!confirm('¿Seguro que deseas eliminar la cita de "' + name + '"?')) {
                return;
            }

            $.ajax({
                url: "{{ url('tattoo-appointments') }}/" + id,
                type: "POST",
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.success) {
                        $('#appointment-row-' + id).remove();

                        var rows = $('#appointmentsTable tbody tr').not('#row-empty-appointments').length;

                        if (rows === 0) {
                            $('#appointmentsTable tbody').html(
                                '<tr id="row-empty-appointments"><td colspan="13" class="text-center text-muted py-4">No hay citas registradas.</td></tr>'
                            );
                        }
                    } else {
                        alert(response.message || 'No se pudo eliminar la cita.');
                    }
                },
                error: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        alert(xhr.responseJSON.message);
                        return;
                    }

                    alert('No se pudo eliminar la cita.');
                }
            });
        });

        modal.on('hidden.bs.modal', function () {
            resetForm();
        });

        detailModal.on('hidden.bs.modal', function () {
            resetDetailModal();
        });
    });
})();
</script>
@endpush