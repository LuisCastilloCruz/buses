@extends('tenant.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <section class="card">
            <header class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="card-title mb-1">Estilos Tattoo</h2>
                    <p class="mb-0 text-muted">Catálogo de estilos de tatuaje</p>
                </div>
                <button class="btn btn-primary" id="btnNewStyle" type="button">
                    + Nuevo estilo
                </button>
            </header>

            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-4 form-group">
                        <label>Buscar estilo</label>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Ej. Fine Line">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group d-flex align-items-end">
                        <button class="btn btn-default mr-2" type="submit">Filtrar</button>
                        <a href="{{ route('tenant.tattoo-styles.index') }}" class="btn btn-light">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="stylesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th width="160">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($styles as $style)
                                <tr id="style-row-{{ $style->id }}">
                                    <td>{{ $style->id }}</td>
                                    <td>{{ $style->name }}</td>
                                    <td>{{ $style->description ?: '-' }}</td>
                                    <td>
                                        @if($style->active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-info btn-edit-style"
                                            data-id="{{ $style->id }}">
                                            Editar
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger btn-delete-style"
                                            data-id="{{ $style->id }}"
                                            data-name="{{ $style->name }}">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No hay estilos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($styles, 'links'))
                <div class="card-footer">
                    {{ $styles->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

<div class="modal fade" id="modalStyle" tabindex="-1" role="dialog" aria-labelledby="styleModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="styleForm">
            @csrf
            <input type="hidden" name="id" id="style_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="styleModalTitle">Nuevo estilo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="styleFormAlert" class="alert alert-danger d-none"></div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Estado</label>
                            <select class="form-control" name="active" id="active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Descripción</label>
                            <textarea class="form-control" rows="4" name="description" id="description" placeholder="Describe el estilo"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" type="submit" id="btnSaveStyle">Guardar estilo</button>
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
        var modal = $('#modalStyle');
        var form = $('#styleForm');
        var alertBox = $('#styleFormAlert');
        var saveBtn = $('#btnSaveStyle');

        function resetForm() {
            if (form.length && form[0]) {
                form[0].reset();
            }
            $('#style_id').val('');
            $('#styleModalTitle').text('Nuevo estilo');
            alertBox.addClass('d-none').html('');
        }

        function showError(message) {
            alertBox.removeClass('d-none').html(message);
        }

        function setLoading(isLoading) {
            saveBtn.prop('disabled', isLoading);
            saveBtn.text(isLoading ? 'Guardando...' : 'Guardar estilo');
        }

        $('#btnNewStyle').on('click', function () {
            resetForm();
            modal.modal('show');
        });

        $(document).on('click', '.btn-edit-style', function () {
            var id = $(this).data('id');
            resetForm();
            $('#styleModalTitle').text('Editar estilo');

            $.ajax({
                url: "{{ url('tattoo-styles/record') }}/" + id,
                type: "GET",
                success: function (response) {
                    $('#style_id').val(response.id || '');
                    $('#name').val(response.name || '');
                    $('#description').val(response.description || '');
                    $('#active').val(response.active ? '1' : '0');
                    modal.modal('show');
                },
                error: function () {
                    alert('No se pudo cargar el estilo.');
                }
            });
        });

        form.on('submit', function (e) {
            e.preventDefault();
            alertBox.addClass('d-none').html('');
            setLoading(true);

            $.ajax({
                url: "{{ route('tenant.tattoo-styles.index') }}",
                type: "POST",
                data: form.serialize(),
                success: function (response) {
                    if (response.success) {
                        modal.modal('hide');
                        window.location.reload();
                    } else {
                        showError(response.message || 'No se pudo guardar el estilo.');
                    }
                },
                error: function (xhr) {
                    var message = 'No se pudo guardar el estilo.';

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

        $(document).on('click', '.btn-delete-style', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');

            if (!confirm('¿Seguro que deseas eliminar el estilo "' + name + '"?')) {
                return;
            }

            $.ajax({
                url: "{{ url('tattoo-styles') }}/" + id,
                type: "POST",
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.success) {
                        $('#style-row-' + id).remove();

                        if ($('#stylesTable tbody tr').length === 0) {
                            $('#stylesTable tbody').html(
                                '<tr><td colspan="5" class="text-center text-muted py-4">No hay estilos registrados.</td></tr>'
                            );
                        }
                    } else {
                        alert(response.message || 'No se pudo eliminar el estilo.');
                    }
                },
                error: function () {
                    alert('No se pudo eliminar el estilo.');
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