@extends('tenant.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <section class="card">
            <header class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="card-title mb-1">Sesiones Tattoo</h2>
                    <p class="mb-0 text-muted">Control de sesiones por cita o proyecto</p>
                </div>
                <button class="btn btn-primary" id="btnNewSession" type="button">
                    + Registrar sesión
                </button>
            </header>

            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-4 form-group">
                        <label>Cliente</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ request('customer_name') }}" placeholder="Buscar cliente">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Artista</label>
                        <select name="artist_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($artists as $artist)
                                <option value="{{ $artist->id }}" {{ request('artist_id') == $artist->id ? 'selected' : '' }}>
                                    {{ $artist->stage_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En proceso</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Finalizada</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Fecha</label>
                        <input type="date" name="session_date" class="form-control" value="{{ request('session_date') }}">
                    </div>

                    <div class="col-md-3 form-group d-flex align-items-end">
                        <button class="btn btn-default mr-2" type="submit">Filtrar</button>
                        <a href="{{ route('tenant.tattoo-sessions.index') }}" class="btn btn-light">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="sessionsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Artista</th>
                                <th>Sesión</th>
                                <th>Pago</th>
                                <th>Costo</th>
                                <th>Estado</th>
                                <th width="170">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                                <tr id="session-row-{{ $session->id }}">
                                    <td>{{ $session->id }}</td>
                                    <td>{{ $session->session_date ?: '-' }}</td>
                                    <td>{{ $session->session_time ?: '-' }}</td>
                                    <td>{{ optional(optional($session->appointment)->person)->name ?: '-' }}</td>
                                    <td>{{ optional(optional($session->appointment)->artist)->stage_name ?: '-' }}</td>
                                    <td>Sesión {{ $session->session_number }}</td>
                                    <td>S/ {{ number_format((float) $session->payment_amount, 2) }}</td>
                                    <td>S/ {{ number_format((float) $session->price, 2) }}</td>
                                    <td>
                                        @if($session->status === 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif($session->status === 'in_progress')
                                            <span class="badge badge-success">En proceso</span>
                                        @elseif($session->status === 'completed')
                                            <span class="badge badge-secondary">Finalizada</span>
                                        @elseif($session->status === 'cancelled')
                                            <span class="badge badge-danger">Cancelada</span>
                                        @else
                                            <span class="badge badge-light">{{ $session->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-info btn-edit-session"
                                            data-id="{{ $session->id }}">
                                            Editar
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger btn-delete-session"
                                            data-id="{{ $session->id }}"
                                            data-name="{{ optional(optional($session->appointment)->person)->name ?: 'la sesión' }}">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No hay sesiones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($sessions, 'links'))
                <div class="card-footer">
                    {{ $sessions->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

<div class="modal fade" id="modalSession" tabindex="-1" role="dialog" aria-labelledby="modalSessionTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="sessionForm">
            @csrf
            <input type="hidden" name="id" id="session_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSessionTitle">Registrar sesión</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="sessionFormAlert" class="alert alert-danger d-none"></div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Cita / Proyecto <span class="text-danger">*</span></label>
                            <select class="form-control" name="tattoo_appointment_id" id="tattoo_appointment_id" required>
                                <option value="">Seleccionar cita</option>
                                @foreach($appointments as $appointment)
                                    <option value="{{ $appointment->id }}">
                                        #{{ $appointment->id }} - {{ optional($appointment->person)->name ?: 'Sin cliente' }} - {{ optional($appointment->artist)->stage_name ?: 'Sin artista' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Número de sesión <span class="text-danger">*</span></label>
                            <input type="number" min="1" class="form-control" name="session_number" id="session_number" value="1" required>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Fecha</label>
                            <input type="date" class="form-control" name="session_date" id="session_date">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Hora</label>
                            <input type="time" class="form-control" name="session_time" id="session_time">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Estado</label>
                            <select class="form-control" name="status" id="status">
                                <option value="pending">Pendiente</option>
                                <option value="in_progress">En proceso</option>
                                <option value="completed">Finalizada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Costo de sesión</label>
                            <input type="number" step="0.01" class="form-control" name="price" id="price" placeholder="0.00">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Monto pagado</label>
                            <input type="number" step="0.01" class="form-control" name="payment_amount" id="payment_amount" placeholder="0.00">
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Notas de sesión</label>
                            <textarea class="form-control" rows="3" name="notes" id="notes" placeholder="Detalles, avance, recomendaciones"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" type="submit" id="btnSaveSession">Guardar sesión</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    $(function () {
        var modal = $('#modalSession');
        var form = $('#sessionForm');
        var alertBox = $('#sessionFormAlert');
        var saveBtn = $('#btnSaveSession');

        function resetForm() {
            if (form.length && form[0]) {
                form[0].reset();
            }
            $('#session_id').val('');
            $('#modalSessionTitle').text('Registrar sesión');
            $('#session_number').val('1');
            $('#status').val('pending');
            alertBox.addClass('d-none').html('');
        }

        function showError(message) {
            alertBox.removeClass('d-none').html(message);
        }

        function setLoading(isLoading) {
            saveBtn.prop('disabled', isLoading);
            saveBtn.text(isLoading ? 'Guardando...' : 'Guardar sesión');
        }

        $('#btnNewSession').on('click', function () {
            resetForm();
            modal.modal('show');
        });

        $(document).on('click', '.btn-edit-session', function () {
            var id = $(this).data('id');
            resetForm();
            $('#modalSessionTitle').text('Editar sesión');

            $.ajax({
                url: "{{ url('tattoo-sessions/record') }}/" + id,
                type: "GET",
                success: function (response) {
                    $('#session_id').val(response.id || '');
                    $('#tattoo_appointment_id').val(response.tattoo_appointment_id || '');
                    $('#session_number').val(response.session_number || 1);
                    $('#session_date').val(response.session_date || '');
                    $('#session_time').val(response.session_time || '');
                    $('#status').val(response.status || 'pending');
                    $('#price').val(response.price || '');
                    $('#payment_amount').val(response.payment_amount || '');
                    $('#notes').val(response.notes || '');
                    modal.modal('show');
                },
                error: function () {
                    alert('No se pudo cargar la sesión.');
                }
            });
        });

        form.on('submit', function (e) {
            e.preventDefault();
            alertBox.addClass('d-none').html('');
            setLoading(true);

            $.ajax({
                url: "{{ route('tenant.tattoo-sessions.index') }}",
                type: "POST",
                data: form.serialize(),
                success: function (response) {
                    if (response.success) {
                        modal.modal('hide');
                        window.location.reload();
                    } else {
                        showError(response.message || 'No se pudo guardar la sesión.');
                    }
                },
                error: function (xhr) {
                    var message = 'No se pudo guardar la sesión.';

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

        $(document).on('click', '.btn-delete-session', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');

            if (!confirm('¿Seguro que deseas eliminar la sesión de "' + name + '"?')) {
                return;
            }

            $.ajax({
                url: "{{ url('tattoo-sessions') }}/" + id,
                type: "POST",
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.success) {
                        $('#session-row-' + id).remove();

                        if ($('#sessionsTable tbody tr').length === 0) {
                            $('#sessionsTable tbody').html(
                                '<tr><td colspan="10" class="text-center text-muted py-4">No hay sesiones registradas.</td></tr>'
                            );
                        }
                    } else {
                        alert(response.message || 'No se pudo eliminar la sesión.');
                    }
                },
                error: function () {
                    alert('No se pudo eliminar la sesión.');
                }
            });
        });

        modal.on('hidden.bs.modal', function () {
            resetForm();
        });
    });
})();
</script>
@endpush