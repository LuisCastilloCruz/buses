@extends('tenant.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <section class="card">
            <header class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="card-title mb-1">Consentimientos Tattoo</h2>
                    <p class="mb-0 text-muted">Gestión de consentimientos y fichas previas</p>
                </div>
                <button class="btn btn-primary" id="btnNewConsent" type="button">
                    + Nuevo consentimiento
                </button>
            </header>

            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-4 form-group">
                        <label>Cliente</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ request('customer_name') }}" placeholder="Buscar cliente">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Estado</label>
                        <select name="accepted" class="form-control">
                            <option value="">Todos</option>
                            <option value="1" {{ request('accepted') === '1' ? 'selected' : '' }}>Firmado</option>
                            <option value="0" {{ request('accepted') === '0' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Fecha de firma</label>
                        <input type="date" name="signed_date" class="form-control" value="{{ request('signed_date') }}">
                    </div>

                    <div class="col-md-3 form-group d-flex align-items-end">
                        <button class="btn btn-default mr-2" type="submit">Filtrar</button>
                        <a href="{{ route('tenant.tattoo-consents.index') }}" class="btn btn-light">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="consentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha firma</th>
                                <th>Cliente</th>
                                <th>Artista</th>
                                <th>Cita</th>
                                <th>Firma</th>
                                <th>Estado</th>
                                <th width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($consents as $consent)
                                <tr id="consent-row-{{ $consent->id }}">
                                    <td>{{ $consent->id }}</td>
                                    <td>{{ $consent->signed_date ?: '-' }}</td>
                                    <td>{{ optional(optional($consent->appointment)->person)->name ?: '-' }}</td>
                                    <td>{{ optional(optional($consent->appointment)->artist)->stage_name ?: '-' }}</td>
                                    <td>#{{ $consent->tattoo_appointment_id }}</td>
                                    <td>{{ $consent->signature_name ?: '-' }}</td>
                                    <td>
                                        @if($consent->accepted)
                                            <span class="badge badge-success">Firmado</span>
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-info btn-edit-consent"
                                            data-id="{{ $consent->id }}">
                                            Editar
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger btn-delete-consent"
                                            data-id="{{ $consent->id }}"
                                            data-name="{{ optional(optional($consent->appointment)->person)->name ?: 'el consentimiento' }}">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No hay consentimientos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($consents, 'links'))
                <div class="card-footer">
                    {{ $consents->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

<div class="modal fade" id="modalConsent" tabindex="-1" role="dialog" aria-labelledby="modalConsentTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="consentForm">
            @csrf
            <input type="hidden" name="id" id="consent_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConsentTitle">Nuevo consentimiento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="consentFormAlert" class="alert alert-danger d-none"></div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Cita / Cliente <span class="text-danger">*</span></label>
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
                            <label>Fecha de firma</label>
                            <input type="date" class="form-control" name="signed_date" id="signed_date">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Alergias</label>
                            <textarea class="form-control" rows="3" name="allergies" id="allergies" placeholder="Alergias, sensibilidad, etc."></textarea>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Notas médicas</label>
                            <textarea class="form-control" rows="3" name="medical_notes" id="medical_notes" placeholder="Diabetes, medicamentos, condiciones, etc."></textarea>
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Observaciones</label>
                            <textarea class="form-control" rows="3" name="observations" id="observations"></textarea>
                        </div>

                        <div class="col-md-8 form-group">
                            <label>Firma / conformidad</label>
                            <input type="text" class="form-control" name="signature_name" id="signature_name" placeholder="Nombre completo del cliente">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Estado</label>
                            <select class="form-control" name="accepted" id="accepted">
                                <option value="0">Pendiente</option>
                                <option value="1">Firmado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
                    <button class="btn btn-success" type="submit" id="btnSaveConsent">Guardar consentimiento</button>
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
        var modal = $('#modalConsent');
        var form = $('#consentForm');
        var alertBox = $('#consentFormAlert');
        var saveBtn = $('#btnSaveConsent');

        function resetForm() {
            if (form.length && form[0]) {
                form[0].reset();
            }
            $('#consent_id').val('');
            $('#modalConsentTitle').text('Nuevo consentimiento');
            $('#accepted').val('0');
            alertBox.addClass('d-none').html('');
        }

        function showError(message) {
            alertBox.removeClass('d-none').html(message);
        }

        function setLoading(isLoading) {
            saveBtn.prop('disabled', isLoading);
            saveBtn.text(isLoading ? 'Guardando...' : 'Guardar consentimiento');
        }

        $('#btnNewConsent').on('click', function () {
            resetForm();
            modal.modal('show');
        });

        $(document).on('click', '.btn-edit-consent', function () {
            var id = $(this).data('id');
            resetForm();
            $('#modalConsentTitle').text('Editar consentimiento');

            $.ajax({
                url: "{{ url('tattoo-consents/record') }}/" + id,
                type: "GET",
                success: function (response) {
                    $('#consent_id').val(response.id || '');
                    $('#tattoo_appointment_id').val(response.tattoo_appointment_id || '');
                    $('#signed_date').val(response.signed_date || '');
                    $('#accepted').val(response.accepted ? '1' : '0');
                    $('#medical_notes').val(response.medical_notes || '');
                    $('#allergies').val(response.allergies || '');
                    $('#observations').val(response.observations || '');
                    $('#signature_name').val(response.signature_name || '');
                    modal.modal('show');
                },
                error: function () {
                    alert('No se pudo cargar el consentimiento.');
                }
            });
        });

        form.on('submit', function (e) {
            e.preventDefault();
            alertBox.addClass('d-none').html('');
            setLoading(true);

            $.ajax({
                url: "{{ route('tenant.tattoo-consents.index') }}",
                type: "POST",
                data: form.serialize(),
                success: function (response) {
                    if (response.success) {
                        modal.modal('hide');
                        window.location.reload();
                    } else {
                        showError(response.message || 'No se pudo guardar el consentimiento.');
                    }
                },
                error: function (xhr) {
                    var message = 'No se pudo guardar el consentimiento.';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            message = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        if (xhr.responseJSON.errors) {
                            var errors = [];
                            Object.keys(xhr.responseJSON.errors).forEach(function (key) {
                                errors = errors.concat(xhr.responseJSON.errors[key]);
                            });
                            message = errors.join('<br>');
                        }
                    } else if (xhr.responseText) {
                        message = xhr.responseText;
                    }

                    showError(message);
                },
                complete: function () {
                    setLoading(false);
                }
            });
        });

        $(document).on('click', '.btn-delete-consent', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');

            if (!confirm('¿Seguro que deseas eliminar el consentimiento de "' + name + '"?')) {
                return;
            }

            $.ajax({
                url: "{{ url('tattoo-consents') }}/" + id,
                type: "POST",
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.success) {
                        $('#consent-row-' + id).remove();

                        if ($('#consentsTable tbody tr').length === 0) {
                            $('#consentsTable tbody').html(
                                '<tr><td colspan="8" class="text-center text-muted py-4">No hay consentimientos registrados.</td></tr>'
                            );
                        }
                    } else {
                        alert(response.message || 'No se pudo eliminar el consentimiento.');
                    }
                },
                error: function () {
                    alert('No se pudo eliminar el consentimiento.');
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