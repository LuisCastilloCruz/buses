@extends('tenant.layouts.app')

@section('content')
    <style>
        .artist-avatar,
        .artist-avatar-placeholder {
            width: 56px !important;
            height: 56px !important;
            min-width: 56px !important;
            max-width: 56px !important;
            min-height: 56px !important;
            max-height: 56px !important;
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }

        .artist-avatar {
            width: 56px !important;
            height: 56px !important;
            min-width: 56px !important;
            max-width: 56px !important;
            min-height: 56px !important;
            max-height: 56px !important;
            object-fit: cover !important;
            display: block !important;
        }

        .artist-avatar-placeholder {
            background: #f1f3f5;
            display: flex !important;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 12px;
            text-align: center;
            overflow: hidden;
        }

        #artistsTable>tbody>tr:not(.portfolio-inline-row)>td:nth-child(2) {
            width: 82px !important;
            min-width: 82px !important;
            max-width: 82px !important;
            text-align: center;
            vertical-align: middle;
            overflow: hidden !important;
        }

        #artistsTable>tbody>tr:not(.portfolio-inline-row)>td:nth-child(2) img.artist-avatar {
            width: 56px !important;
            height: 56px !important;
            min-width: 56px !important;
            max-width: 56px !important;
            min-height: 56px !important;
            max-height: 56px !important;
            object-fit: cover !important;
            display: inline-block !important;
            margin: 0 auto !important;
        }

        #artistsTable {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse;
        }

        #artistsTable th,
        #artistsTable td {
            vertical-align: middle !important;
        }

        #artistsTable>thead>tr>th:nth-child(1) {
            width: 60px !important;
        }

        #artistsTable>thead>tr>th:nth-child(2) {
            width: 82px !important;
        }

        #artistsTable>thead>tr>th:nth-child(3) {
            width: 220px !important;
        }

        #artistsTable>thead>tr>th:nth-child(4) {
            width: 220px !important;
        }

        #artistsTable>thead>tr>th:nth-child(5) {
            width: 150px !important;
        }

        #artistsTable>thead>tr>th:nth-child(6) {
            width: 130px !important;
        }

        #artistsTable>thead>tr>th:nth-child(7) {
            width: 110px !important;
        }

        #artistsTable>thead>tr>th:nth-child(8) {
            width: 90px !important;
        }

        #artistsTable>thead>tr>th:nth-child(9) {
            width: 380px !important;
        }

        #artistsTable>tbody>tr:not(.portfolio-inline-row)>td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .portfolio-counter-badge {
            font-size: 12px;
            padding: .38rem .7rem;
            border-radius: 999px;
            white-space: nowrap;
            font-weight: 600;
        }

        .artist-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            align-items: center;
        }

        .artist-actions .btn {
            margin: 0 !important;
            white-space: nowrap;
            border-radius: 10px;
            font-weight: 500;
        }

        .btn-toggle-works .toggle-label-hide {
            display: none;
        }

        .btn-toggle-works.expanded .toggle-label-show {
            display: none;
        }

        .btn-toggle-works.expanded .toggle-label-hide {
            display: inline;
        }

        .portfolio-inline-row {
            background: #f8fafc;
            display: none;
        }

        .portfolio-inline-row>td {
            padding: 0 !important;
            overflow: visible !important;
        }

        .portfolio-inline-shell {
            padding: 1.1rem 1.1rem 1rem 1.1rem;
            border-top: 1px solid #edf2f7;
            background: linear-gradient(180deg, #fbfdff 0%, #f7f9fc 100%);
        }

        .portfolio-inline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: .9rem 1rem;
            border: 1px solid #e8edf3;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(15, 23, 42, .03);
        }

        .portfolio-inline-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .portfolio-inline-subtitle {
            margin: .2rem 0 0;
            font-size: 12px;
            color: #6b7280;
        }

        .portfolio-inline-tools {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .portfolio-count-chip {
            display: inline-flex;
            align-items: center;
            padding: .38rem .7rem;
            border-radius: 999px;
            background: #111827;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .portfolio-item {
            min-width: 0;
        }

        .portfolio-card {
            height: 100%;
            border: 1px solid #e8edf3;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .portfolio-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, .08);
        }

        .portfolio-thumb-wrap {
            position: relative;
            height: 190px;
            overflow: hidden;
            background: #f3f4f6;
        }

        .portfolio-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .35s ease;
            cursor: zoom-in;
        }

        .portfolio-card:hover .portfolio-thumb {
            transform: scale(1.06);
        }

        .portfolio-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, .52), rgba(0, 0, 0, .08));
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: .8rem;
            opacity: 0;
            transition: opacity .25s ease;
        }

        .portfolio-thumb-wrap:hover .portfolio-overlay {
            opacity: 1;
        }

        .portfolio-card-body {
            padding: .95rem;
        }

        .portfolio-card-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: .4rem;
            min-height: 40px;
            line-height: 1.35;
        }

        .portfolio-card-text {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.45;
            min-height: 38px;
            margin-bottom: .7rem;
        }

        .portfolio-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-bottom: .75rem;
        }

        .portfolio-meta .badge {
            font-size: 11px;
            padding: .38rem .55rem;
            border-radius: 999px;
        }

        .portfolio-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
        }

        .portfolio-card-actions .btn {
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            padding: .38rem .7rem;
            margin: 0;
        }

        .portfolio-empty-box {
            border: 1px dashed #ced4da;
            border-radius: 14px;
            padding: 2rem;
            text-align: center;
            color: #6c757d;
            background: #fbfbfc;
        }




        #modalPortfolioGallery .modal-dialog {
            width: 96vw !important;
            max-width: 1450px !important;
            margin: 16px auto !important;
        }

        #modalPortfolioGallery .modal-content {
            border: 0 !important;
            border-radius: 20px !important;
            overflow: hidden !important;
            background: #ffffff !important;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .18) !important;
        }

        #modalPortfolioGallery .modal-header {
            background: #ffffff !important;
            border-bottom: 1px solid #e9eef5 !important;
            padding: 1rem 1.25rem !important;
        }

        #modalPortfolioGallery .modal-title {
            font-size: 20px !important;
            font-weight: 700 !important;
            color: #111827 !important;
        }

        #modalPortfolioGallery .modal-body {
            background: #f8fafc !important;
            padding: 1.1rem !important;
            max-height: calc(100vh - 140px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        #portfolioGalleryContainer {
            width: 100% !important;
        }

        .portfolio-popup-grid {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 1rem !important;
            width: 100% !important;
            align-items: stretch !important;
        }

        .portfolio-popup-card {
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 18px !important;
            overflow: hidden !important;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .06) !important;
            display: flex !important;
            flex-direction: column !important;
            min-width: 0 !important;
            height: 100% !important;
            transition: transform .18s ease, box-shadow .18s ease !important;
        }

        .portfolio-popup-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .10) !important;
        }

        .portfolio-popup-thumb-wrap {
            position: relative !important;
            width: 100% !important;
            height: 260px !important;
            overflow: hidden !important;
            background: #eef2f7 !important;
            flex-shrink: 0 !important;
        }

        .portfolio-popup-thumb {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center center !important;
            display: block !important;
            cursor: zoom-in !important;
            transition: transform .28s ease !important;
            background: #eef2f7 !important;
        }

        .portfolio-popup-card:hover .portfolio-popup-thumb {
            transform: scale(1.04) !important;
        }

        .portfolio-popup-overlay {
            position: absolute !important;
            inset: 0 !important;
            background: linear-gradient(to top, rgba(0, 0, 0, .56), rgba(0, 0, 0, .08)) !important;
            display: flex !important;
            align-items: flex-end !important;
            justify-content: center !important;
            padding: .85rem !important;
            opacity: 0 !important;
            transition: opacity .22s ease !important;
        }

        .portfolio-popup-thumb-wrap:hover .portfolio-popup-overlay {
            opacity: 1 !important;
        }

        .portfolio-popup-body {
            padding: 1rem !important;
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            min-height: 190px !important;
        }

        .portfolio-popup-title {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #111827 !important;
            line-height: 1.35 !important;
            margin-bottom: .45rem !important;
            min-height: 40px !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        .portfolio-popup-text {
            font-size: 12px !important;
            color: #6b7280 !important;
            line-height: 1.5 !important;
            margin-bottom: .8rem !important;
            min-height: 56px !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        .portfolio-popup-meta {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: .45rem !important;
            margin-bottom: .85rem !important;
        }

        .portfolio-popup-meta .badge {
            font-size: 11px !important;
            padding: .4rem .62rem !important;
            border-radius: 999px !important;
        }

        .portfolio-popup-actions {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: .45rem !important;
            margin-top: auto !important;
        }

        .portfolio-popup-actions .btn,
        .portfolio-popup-overlay .btn {
            border-radius: 10px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            padding: .4rem .75rem !important;
            margin: 0 !important;
        }

        .portfolio-popup-empty {
            border: 1px dashed #ced4da !important;
            border-radius: 16px !important;
            padding: 2.5rem 1.5rem !important;
            text-align: center !important;
            color: #6c757d !important;
            background: #ffffff !important;
        }

        @media (max-width: 1199px) {
            .portfolio-popup-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 767px) {
            #modalPortfolioGallery .modal-dialog {
                width: calc(100vw - 12px) !important;
                max-width: calc(100vw - 12px) !important;
                margin: 6px auto !important;
            }

            #modalPortfolioGallery .modal-body {
                padding: .8rem !important;
            }

            .portfolio-popup-grid {
                grid-template-columns: 1fr !important;
            }

            .portfolio-popup-thumb-wrap {
                height: 230px !important;
            }
        }
    </style>

    <div class="row">
        <div class="col-12">
            <section class="card">
                <header class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="card-title mb-1">Artistas Tattoo</h2>
                        <p class="mb-0 text-muted">Administración de tatuadores, foto de perfil y portafolio de trabajos</p>
                    </div>
                    <button class="btn btn-primary" id="btnNewArtist" type="button">
                        + Nuevo artista
                    </button>
                </header>

                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-4 form-group">
                            <label>Buscar por nombre artístico</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                placeholder="Ej. Ink Luis">
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
                            <a href="{{ route('tenant.tattoo-artists.index') }}" class="btn btn-light">Limpiar</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 no-auto-datatable" id="artistsTable" data-no-responsive="1">
                            <thead>
                                <tr>
                                    <th style="width:60px;">ID</th>
                                    <th style="width:82px;">Foto</th>
                                    <th style="width:220px;">Nombre artístico</th>
                                    <th style="width:220px;">Usuario</th>
                                    <th style="width:150px;">Especialidad</th>
                                    <th style="width:130px;">Instagram</th>
                                    <th style="width:110px;">Portafolio</th>
                                    <th style="width:90px;">Estado</th>
                                    <th style="width:380px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($artists as $artist)
                                    @php

                                        $portfolios = $artist->portfolios ?? collect();
                                        $portfolioCount = $portfolios->count();

                                        $portfolioData = $portfolios
                                            ->map(function ($portfolio) use ($artist) {
                                                $imageUrl = '';

                                                if (!empty($portfolio->image_path)) {
                                                    $imageUrl = Storage::url($portfolio->image_path);
                                                }

                                                return [
                                                    'id' => $portfolio->id,
                                                    'artist_id' => $artist->id,
                                                    'artist' => $artist->stage_name,
                                                    'title' => !empty($portfolio->title)
                                                        ? $portfolio->title
                                                        : 'Trabajo sin título',
                                                    'description' => !empty($portfolio->description)
                                                        ? $portfolio->description
                                                        : 'Sin descripción',
                                                    'image' => $imageUrl,
                                                    'active' => (int) ($portfolio->active ?? 0),
                                                    'order' => (int) ($portfolio->sort_order ?? 0),
                                                ];
                                            })
                                            ->values()
                                            ->all();
                                    @endphp


                                    <tr id="artist-row-{{ $artist->id }}">
                                        <td>{{ $artist->id }}</td>

                                        <td class="text-center">
                                            @if ($artist->photo_url)
                                                <img src="{{ $artist->photo_url }}" alt="{{ $artist->stage_name }}"
                                                    class="artist-avatar">
                                            @else
                                                <div class="artist-avatar-placeholder">
                                                    Sin foto
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            <strong>{{ $artist->stage_name }}</strong>
                                            @if ($artist->bio)
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Illuminate\Support\Str::limit($artist->bio, 80) }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($artist->user)
                                                {{ $artist->user->name }}<br>
                                                <small class="text-muted">{{ $artist->user->email }}</small>
                                            @else
                                                <span class="text-muted">Sin usuario</span>
                                            @endif
                                        </td>

                                        <td>{{ $artist->specialty ?: '-' }}</td>

                                        <td>
                                            @if ($artist->instagram)
                                                <span>{{ $artist->instagram }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge badge-dark portfolio-counter-badge">
                                                {{ $portfolioCount }} {{ $portfolioCount === 1 ? 'trabajo' : 'trabajos' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if ($artist->active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="artist-actions">
                                                <button type="button" class="btn btn-sm btn-info btn-edit-artist"
                                                    data-id="{{ $artist->id }}">
                                                    Editar
                                                </button>

                                                <button type="button" class="btn btn-sm btn-warning btn-manage-portfolio"
                                                    data-id="{{ $artist->id }}" data-name="{{ $artist->stage_name }}">
                                                    Portafolio
                                                </button>




                                                @if ($portfolioCount > 0)
                                                    <button type="button"
                                                        class="btn btn-sm btn-dark btn-open-portfolio-modal"
                                                        data-artist-id="{{ $artist->id }}"
                                                        data-artist-name="{{ $artist->stage_name }}"
                                                        data-portfolios='@json($portfolioData)'>
                                                        Ver popup
                                                    </button>
                                                @endif



                                                <button type="button" class="btn btn-sm btn-danger btn-delete-artist"
                                                    data-id="{{ $artist->id }}" data-name="{{ $artist->stage_name }}">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No hay artistas registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if (method_exists($artists, 'links'))
                    <div class="card-footer">
                        {{ $artists->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div class="modal fade" id="modalArtist" tabindex="-1" role="dialog" aria-labelledby="artistModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="artistForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="artist_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="artistModalTitle">Nuevo artista</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div id="artistFormAlert" class="alert alert-danger d-none"></div>

                        <div class="row">
                            <div class="col-md-4 form-group text-center">
                                <label class="d-block">Foto del artista</label>
                                <img id="artistPhotoPreview" src="" alt="Preview"
                                    style="width: 160px; height: 160px; object-fit: cover; border-radius: 16px; border: 1px solid #dee2e6; background: #f8f9fa; display: none; margin: 0 auto 10px auto;">

                                <div id="artistPhotoPlaceholder"
                                    style="width: 160px; height: 160px; border-radius: 16px; border: 1px dashed #ced4da; background: #f8f9fa; display:flex; align-items:center; justify-content:center; color:#6c757d; margin: 0 auto 10px auto;">
                                    Sin foto
                                </div>

                                <input type="file" class="form-control-file" name="photo" id="photo"
                                    accept=".jpg,.jpeg,.png,.webp,image/*">
                                <small class="text-muted d-block mt-1">JPG, PNG o WEBP. Máx. 4 MB.</small>
                            </div>

                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label>Usuario del sistema</label>
                                        <select class="form-control" name="user_id" id="user_id">
                                            <option value="">Seleccionar usuario</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">
                                                    {{ $user->name }} - {{ $user->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12 form-group">
                                        <label>Nombre artístico <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="stage_name" id="stage_name"
                                            required>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Especialidad</label>
                                        <input type="text" class="form-control" name="specialty" id="specialty"
                                            placeholder="Ej. Fine line">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Instagram</label>
                                        <input type="text" class="form-control" name="instagram" id="instagram"
                                            placeholder="@usuario">
                                    </div>

                                    <div class="col-md-12 form-group">
                                        <label>Biografía</label>
                                        <textarea class="form-control" rows="4" name="bio" id="bio"></textarea>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Estado</label>
                                        <select class="form-control" name="active" id="active">
                                            <option value="1">Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
                        <button class="btn btn-primary" type="submit" id="btnSaveArtist">Guardar artista</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalPortfolio" tabindex="-1" role="dialog" aria-labelledby="portfolioModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="portfolioForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="portfolio_id">
                <input type="hidden" name="tattoo_artist_id" id="portfolio_tattoo_artist_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="portfolioModalTitle">Nuevo trabajo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div id="portfolioFormAlert" class="alert alert-danger d-none"></div>

                        <div class="alert alert-info">
                            Artista: <strong id="portfolioArtistName">-</strong>
                        </div>

                        <div class="row">
                            <div class="col-md-5 form-group text-center">
                                <label class="d-block">Imagen del trabajo</label>
                                <img id="portfolioImagePreview" src="" alt="Preview"
                                    style="width: 100%; max-width: 260px; height: 260px; object-fit: cover; border-radius: 14px; border: 1px solid #dee2e6; background: #f8f9fa; display: none; margin: 0 auto 10px auto;">

                                <div id="portfolioImagePlaceholder"
                                    style="width: 100%; max-width: 260px; height: 260px; border-radius: 14px; border: 1px dashed #ced4da; background: #f8f9fa; display:flex; align-items:center; justify-content:center; color:#6c757d; margin: 0 auto 10px auto;">
                                    Sin imagen
                                </div>

                                <input type="file" class="form-control-file" name="image" id="portfolio_image"
                                    accept=".jpg,.jpeg,.png,.webp,image/*">
                                <small class="text-muted d-block mt-1">JPG, PNG o WEBP. Máx. 6 MB.</small>
                            </div>

                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Título</label>
                                    <input type="text" class="form-control" name="title" id="portfolio_title"
                                        placeholder="Ej. Dragón japonés">
                                </div>

                                <div class="form-group">
                                    <label>Descripción breve</label>
                                    <textarea class="form-control" rows="5" name="description" id="portfolio_description"
                                        placeholder="Detalle del diseño, técnica, zona, estilo, colores, etc."></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Orden</label>
                                    <input type="number" class="form-control" name="sort_order"
                                        id="portfolio_sort_order" min="0" value="0">
                                </div>

                                <div class="form-group">
                                    <label>Estado</label>
                                    <select class="form-control" name="active" id="portfolio_active">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
                        <button class="btn btn-warning" type="submit" id="btnSavePortfolio">Guardar trabajo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalPortfolioGallery" tabindex="-1" role="dialog"
        aria-labelledby="portfolioGalleryTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="portfolioGalleryTitle">Portafolio del artista</h5>
                        <small class="text-muted" id="portfolioGallerySubtitle">Trabajos registrados</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="portfolioGalleryContainer">
                        <div class="portfolio-popup-empty">No hay trabajos para mostrar.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPortfolioViewer" tabindex="-1" role="dialog"
        aria-labelledby="portfolioViewerTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="portfolioViewerTitle">Detalle del trabajo</h5>
                        <small class="text-muted" id="portfolioViewerArtist">Artista</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-8 mb-3 mb-lg-0">
                            <div class="portfolio-detail-image-wrap">
                                <img id="portfolioViewerImage" src="" alt="Trabajo"
                                    class="portfolio-detail-image">
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <h4 id="portfolioViewerTitleText" class="mb-2">Trabajo sin título</h4>
                            <p id="portfolioViewerDescription" class="text-muted">Sin descripción</p>

                            <div class="mb-3">
                                <span id="portfolioViewerStatus" class="badge badge-success mr-2">Activo</span>
                                <span id="portfolioViewerOrder" class="badge badge-light">Orden: 0</span>
                            </div>

                            <div class="d-flex flex-wrap">
                                <a id="portfolioViewerOpenImage" href="#" target="_blank"
                                    class="btn btn-dark mr-2 mb-2">
                                    Abrir imagen
                                </a>
                                <button type="button" class="btn btn-outline-secondary mb-2" data-dismiss="modal">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            $(function() {
                var modalArtist = $('#modalArtist');
                var formArtist = $('#artistForm');
                var artistAlertBox = $('#artistFormAlert');
                var saveArtistBtn = $('#btnSaveArtist');

                var modalPortfolio = $('#modalPortfolio');
                var formPortfolio = $('#portfolioForm');
                var portfolioAlertBox = $('#portfolioFormAlert');
                var savePortfolioBtn = $('#btnSavePortfolio');

                var modalPortfolioGallery = $('#modalPortfolioGallery');
                var modalPortfolioViewer = $('#modalPortfolioViewer');

                function killArtistsTablePlugins() {
                    var $table = $('#artistsTable');

                    if (!$table.length) return;

                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#artistsTable')) {
                        $table.DataTable().destroy();
                    }

                    $table.removeClass('dataTable no-footer dtr-inline collapsed');
                    $table.removeAttr('style');
                    $table.find('thead, tbody, tr, th, td').removeAttr('style');
                    $table.find('colgroup').remove();

                    $table.find('> tbody > tr:not(.portfolio-inline-row) > td:nth-child(2) img.artist-avatar')
                        .each(function() {
                            $(this).css({
                                width: '56px',
                                height: '56px',
                                minWidth: '56px',
                                maxWidth: '56px',
                                minHeight: '56px',
                                maxHeight: '56px',
                                objectFit: 'cover',
                                display: 'inline-block'
                            });
                        });

                    var $wrapper = $table.closest('.dataTables_wrapper');
                    if ($wrapper.length) {
                        $wrapper.find(
                            '> .dataTables_length, > .dataTables_filter, > .dataTables_info, > .dataTables_paginate'
                        ).remove();

                        if ($wrapper.find('table#artistsTable').length) {
                            $wrapper.replaceWith($table);
                        }
                    }
                }

                killArtistsTablePlugins();
                setTimeout(killArtistsTablePlugins, 200);
                setTimeout(killArtistsTablePlugins, 800);
                $(window).on('load', killArtistsTablePlugins);

                function escapeHtml(text) {
                    if (text === null || text === undefined) return '';
                    return String(text)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function nl2br(text) {
                    return escapeHtml(text).replace(/\n/g, '<br>');
                }

                function readImage(input, previewSelector, placeholderSelector) {
                    if (!input || !input.files || !input.files[0]) {
                        $(previewSelector).attr('src', '').hide();
                        $(placeholderSelector).show();
                        return;
                    }

                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewSelector).attr('src', e.target.result).show();
                        $(placeholderSelector).hide();
                    };
                    reader.readAsDataURL(input.files[0]);
                }

                function resetArtistPreview() {
                    $('#artistPhotoPreview').attr('src', '').hide();
                    $('#artistPhotoPlaceholder').show();
                    $('#photo').val('');
                }

                function resetPortfolioPreview() {
                    $('#portfolioImagePreview').attr('src', '').hide();
                    $('#portfolioImagePlaceholder').show();
                    $('#portfolio_image').val('');
                }

                function resetArtistForm() {
                    if (formArtist.length && formArtist[0]) {
                        formArtist[0].reset();
                    }

                    $('#artist_id').val('');
                    $('#artistModalTitle').text('Nuevo artista');
                    $('#active').val('1');
                    artistAlertBox.addClass('d-none').html('');
                    resetArtistPreview();
                }

                function resetPortfolioForm() {
                    if (formPortfolio.length && formPortfolio[0]) {
                        formPortfolio[0].reset();
                    }

                    $('#portfolio_id').val('');
                    $('#portfolio_tattoo_artist_id').val('');
                    $('#portfolioArtistName').text('-');
                    $('#portfolioModalTitle').text('Nuevo trabajo');
                    $('#portfolio_sort_order').val('0');
                    $('#portfolio_active').val('1');
                    portfolioAlertBox.addClass('d-none').html('');
                    resetPortfolioPreview();
                }

                function showArtistError(message) {
                    artistAlertBox.removeClass('d-none').html(message);
                }

                function showPortfolioError(message) {
                    portfolioAlertBox.removeClass('d-none').html(message);
                }

                function setArtistLoading(isLoading) {
                    saveArtistBtn.prop('disabled', isLoading);
                    saveArtistBtn.text(isLoading ? 'Guardando...' : 'Guardar artista');
                }

                function setPortfolioLoading(isLoading) {
                    savePortfolioBtn.prop('disabled', isLoading);
                    savePortfolioBtn.text(isLoading ? 'Guardando...' : 'Guardar trabajo');
                }



                function buildPortfolioPopupHtml(portfolios) {
                    if (!Array.isArray(portfolios) || !portfolios.length) {
                        return '<div class="portfolio-popup-empty">No hay trabajos para mostrar.</div>';
                    }

                    var sorted = portfolios.slice().sort(function(a, b) {
                        return (parseInt(a.order || 0, 10)) - (parseInt(b.order || 0, 10));
                    });

                    var html = '<div class="portfolio-popup-grid">';

                    sorted.forEach(function(item) {
                        var id = item.id || '';
                        var artist = item.artist || 'Artista';
                        var title = item.title || 'Trabajo sin título';
                        var description = item.description || 'Sin descripción';
                        var image = item.image || '';
                        var active = String(item.active || '0') === '1';
                        var order = item.order || 0;
                        var artistId = item.artist_id || '';

                        var shortDescription = description.length > 130 ?
                            description.substring(0, 130) + '...' :
                            description;

                        html += '<div class="portfolio-popup-card" id="portfolio-card-' + id + '">';

                        html += '  <div class="portfolio-popup-thumb-wrap">';
                        if (image) {
                            html += '      <img src="' + escapeHtml(image) + '" alt="' + escapeHtml(
                                    title) + '" class="portfolio-popup-thumb btn-view-work" ' +
                                'data-id="' + escapeHtml(id) + '" ' +
                                'data-artist="' + escapeHtml(artist) + '" ' +
                                'data-title="' + escapeHtml(title) + '" ' +
                                'data-description="' + escapeHtml(description) + '" ' +
                                'data-image="' + escapeHtml(image) + '" ' +
                                'data-active="' + (active ? '1' : '0') + '" ' +
                                'data-order="' + escapeHtml(order) + '">';

                            html += '      <div class="portfolio-popup-overlay">';
                            html +=
                                '          <button type="button" class="btn btn-sm btn-light btn-view-work" ' +
                                'data-id="' + escapeHtml(id) + '" ' +
                                'data-artist="' + escapeHtml(artist) + '" ' +
                                'data-title="' + escapeHtml(title) + '" ' +
                                'data-description="' + escapeHtml(description) + '" ' +
                                'data-image="' + escapeHtml(image) + '" ' +
                                'data-active="' + (active ? '1' : '0') + '" ' +
                                'data-order="' + escapeHtml(order) + '">' +
                                'Ver detalle</button>';
                            html += '      </div>';
                        } else {
                            html +=
                                '      <div class="d-flex align-items-center justify-content-center h-100 text-muted">Sin imagen</div>';
                        }
                        html += '  </div>';

                        html += '  <div class="portfolio-popup-body">';
                        html += '      <div class="portfolio-popup-title">' + escapeHtml(title) +
                            '</div>';
                        html += '      <div class="portfolio-popup-text">' + escapeHtml(
                            shortDescription) + '</div>';
                        html += '      <div class="portfolio-popup-meta">';
                        html += '          <span class="badge ' + (active ? 'badge-success' :
                            'badge-secondary') + '">' + (active ? 'Activo' : 'Inactivo') + '</span>';
                        html += '          <span class="badge badge-light">Orden: ' + escapeHtml(
                            order) + '</span>';
                        html += '      </div>';
                        html += '      <div class="portfolio-popup-actions">';
                        html +=
                            '          <button type="button" class="btn btn-sm btn-outline-dark btn-view-work" ' +
                            'data-id="' + escapeHtml(id) + '" ' +
                            'data-artist="' + escapeHtml(artist) + '" ' +
                            'data-title="' + escapeHtml(title) + '" ' +
                            'data-description="' + escapeHtml(description) + '" ' +
                            'data-image="' + escapeHtml(image) + '" ' +
                            'data-active="' + (active ? '1' : '0') + '" ' +
                            'data-order="' + escapeHtml(order) + '">' +
                            'Ver detalle</button>';

                        html +=
                            '          <button type="button" class="btn btn-sm btn-outline-info btn-edit-portfolio" ' +
                            'data-id="' + escapeHtml(id) + '" ' +
                            'data-artist-id="' + escapeHtml(artistId) + '">' +
                            'Editar</button>';

                        html +=
                            '          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-portfolio" ' +
                            'data-id="' + escapeHtml(id) + '" ' +
                            'data-name="' + escapeHtml(title) + '">' +
                            'Eliminar</button>';
                        html += '      </div>';
                        html += '  </div>';

                        html += '</div>';
                    });

                    html += '</div>';
                    return html;
                }

                function openPortfolioGallery(artistId, artistName, portfolios) {
                    $('#portfolioGalleryTitle').text('Portafolio de ' + artistName);
                    $('#portfolioGallerySubtitle').text((portfolios.length || 0) + ' trabajo(s) registrados');
                    $('#portfolioGalleryContainer').html(buildPortfolioPopupHtml(portfolios));

                    modalPortfolioGallery.modal({
                        backdrop: true,
                        keyboard: true,
                        show: true
                    });
                }

                $(document).on('click', '.btn-open-portfolio-modal', function() {
                    var artistId = $(this).data('artist-id');
                    var artistName = $(this).data('artist-name') || 'Artista';
                    var portfolios = $(this).attr('data-portfolios') || '[]';

                    try {
                        portfolios = JSON.parse(portfolios);
                    } catch (e) {
                        console.error('Error parseando portfolios:', e, portfolios);
                        portfolios = [];
                    }

                    console.log('PORTFOLIOS DEL POPUP:', portfolios);

                    openPortfolioGallery(artistId, artistName, portfolios);
                });





                function openWorkViewer(data) {
                    var title = data.title || 'Trabajo sin título';
                    var description = data.description || 'Sin descripción';
                    var image = data.image || '';
                    var artist = data.artist || 'Artista';
                    var active = String(data.active || '0') === '1';
                    var order = data.order || 0;

                    $('#portfolioViewerTitle').text(title);
                    $('#portfolioViewerArtist').text('Artista: ' + artist);
                    $('#portfolioViewerTitleText').text(title);
                    $('#portfolioViewerDescription').html(nl2br(description));
                    $('#portfolioViewerImage').attr('src', image);
                    $('#portfolioViewerOpenImage').attr('href', image);
                    $('#portfolioViewerOrder').text('Orden: ' + order);

                    $('#portfolioViewerStatus')
                        .removeClass('badge-success badge-secondary')
                        .addClass(active ? 'badge-success' : 'badge-secondary')
                        .text(active ? 'Activo' : 'Inactivo');

                    modalPortfolioViewer.modal('show');
                }

                $('#photo').on('change', function() {
                    readImage(this, '#artistPhotoPreview', '#artistPhotoPlaceholder');
                });

                $('#portfolio_image').on('change', function() {
                    readImage(this, '#portfolioImagePreview', '#portfolioImagePlaceholder');
                });

                $('#btnNewArtist').on('click', function() {
                    resetArtistForm();
                    modalArtist.modal('show');
                });

                $(document).on('click', '.btn-edit-artist', function() {
                    var id = $(this).data('id');
                    resetArtistForm();
                    $('#artistModalTitle').text('Editar artista');

                    $.ajax({
                        url: "{{ url('tattoo-artists/record') }}/" + id,
                        type: "GET",
                        success: function(response) {
                            $('#artist_id').val(response.id || '');
                            $('#user_id').val(response.user_id || '');
                            $('#stage_name').val(response.stage_name || '');
                            $('#specialty').val(response.specialty || '');
                            $('#instagram').val(response.instagram || '');
                            $('#bio').val(response.bio || '');
                            $('#active').val(response.active ? '1' : '0');

                            if (response.photo_url) {
                                $('#artistPhotoPreview').attr('src', response.photo_url)
                                    .show();
                                $('#artistPhotoPlaceholder').hide();
                            } else {
                                resetArtistPreview();
                            }

                            modalArtist.modal('show');
                        },
                        error: function() {
                            alert('No se pudo cargar el artista.');
                        }
                    });
                });

                formArtist.on('submit', function(e) {
                    e.preventDefault();
                    artistAlertBox.addClass('d-none').html('');
                    setArtistLoading(true);

                    var formData = new FormData(formArtist[0]);

                    $.ajax({
                        url: "{{ route('tenant.tattoo-artists.index') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                modalArtist.modal('hide');
                                window.location.reload();
                            } else {
                                showArtistError(response.message ||
                                    'No se pudo guardar el artista.');
                            }
                        },
                        error: function(xhr) {
                            var message = 'No se pudo guardar el artista.';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }

                                if (xhr.responseJSON.errors) {
                                    var errors = [];
                                    Object.keys(xhr.responseJSON.errors).forEach(function(
                                        key) {
                                        errors = errors.concat(xhr.responseJSON
                                            .errors[key]);
                                    });
                                    message = errors.join('<br>');
                                }
                            }

                            showArtistError(message);
                        },
                        complete: function() {
                            setArtistLoading(false);
                        }
                    });
                });

                $(document).on('click', '.btn-manage-portfolio', function() {
                    resetPortfolioForm();
                    $('#portfolio_tattoo_artist_id').val($(this).data('id'));
                    $('#portfolioArtistName').text($(this).data('name'));
                    $('#portfolioModalTitle').text('Nuevo trabajo');
                    modalPortfolio.modal('show');
                });

                $(document).on('click', '.btn-edit-portfolio', function() {
                    var id = $(this).data('id');
                    var artistId = $(this).data('artist-id');
                    resetPortfolioForm();
                    $('#portfolioModalTitle').text('Editar trabajo');

                    $.ajax({
                        url: "{{ url('tattoo-artists/portfolio/record') }}/" + id,
                        type: "GET",
                        success: function(response) {
                            $('#portfolio_id').val(response.id || '');
                            $('#portfolio_tattoo_artist_id').val(response
                                .tattoo_artist_id || artistId || '');
                            $('#portfolio_title').val(response.title || '');
                            $('#portfolio_description').val(response.description || '');
                            $('#portfolio_sort_order').val(response.sort_order || 0);
                            $('#portfolio_active').val(response.active ? '1' : '0');

                            var artistName = response.artist_name || ('Artista #' + (
                                response.tattoo_artist_id || artistId || ''));
                            $('#portfolioArtistName').text(artistName);

                            if (response.image_url) {
                                $('#portfolioImagePreview').attr('src', response.image_url)
                                    .show();
                                $('#portfolioImagePlaceholder').hide();
                            } else {
                                resetPortfolioPreview();
                            }

                            modalPortfolio.modal('show');
                        },
                        error: function() {
                            alert('No se pudo cargar el trabajo.');
                        }
                    });
                });

                formPortfolio.on('submit', function(e) {
                    e.preventDefault();
                    portfolioAlertBox.addClass('d-none').html('');
                    setPortfolioLoading(true);

                    var formData = new FormData(formPortfolio[0]);

                    $.ajax({
                        url: "{{ route('tenant.tattoo-artists.portfolio.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                modalPortfolio.modal('hide');
                                window.location.reload();
                            } else {
                                showPortfolioError(response.message ||
                                    'No se pudo guardar el trabajo.');
                            }
                        },
                        error: function(xhr) {
                            var message = 'No se pudo guardar el trabajo.';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }

                                if (xhr.responseJSON.errors) {
                                    var errors = [];
                                    Object.keys(xhr.responseJSON.errors).forEach(function(
                                        key) {
                                        errors = errors.concat(xhr.responseJSON
                                            .errors[key]);
                                    });
                                    message = errors.join('<br>');
                                }
                            }

                            showPortfolioError(message);
                        },
                        complete: function() {
                            setPortfolioLoading(false);
                        }
                    });
                });

                $(document).on('click', '.btn-delete-artist', function() {
                    var id = $(this).data('id');
                    var name = $(this).data('name');

                    if (!confirm('¿Seguro que deseas eliminar al artista "' + name +
                            '"? También se eliminará su portafolio.')) {
                        return;
                    }

                    $.ajax({
                        url: "{{ url('tattoo-artists') }}/" + id,
                        type: "POST",
                        data: {
                            _method: 'DELETE',
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                alert(response.message ||
                                    'No se pudo eliminar el artista.');
                            }
                        },
                        error: function() {
                            alert('No se pudo eliminar el artista.');
                        }
                    });
                });

                $(document).on('click', '.btn-delete-portfolio', function() {
                    var id = $(this).data('id');
                    var name = $(this).data('name');

                    if (!confirm('¿Seguro que deseas eliminar "' + name + '" del portafolio?')) {
                        return;
                    }

                    $.ajax({
                        url: "{{ url('tattoo-artists/portfolio') }}/" + id,
                        type: "POST",
                        data: {
                            _method: 'DELETE',
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#portfolio-card-' + id).remove();

                                var galleryCard = $('#portfolioGalleryContainer').find(
                                    '#portfolio-card-' + id);
                                if (galleryCard.length) {
                                    galleryCard.remove();
                                }

                                if (!$('#portfolioGalleryContainer').find(
                                        '.portfolio-popup-card').length) {
                                    $('#portfolioGalleryContainer').html(
                                        '<div class="portfolio-popup-empty">No hay trabajos para mostrar.</div>'
                                    );
                                }
                            } else {
                                alert(response.message ||
                                    'No se pudo eliminar el trabajo.');
                            }
                        },
                        error: function() {
                            alert('No se pudo eliminar el trabajo.');
                        }
                    });
                });

                $(document).on('click', '.btn-toggle-works', function() {
                    var btn = $(this);
                    var artistId = btn.data('artist-id');
                    var target = $('#portfolio-inline-row-' + artistId);

                    if (!target.length) return;

                    target.stop(true, true).slideToggle(180, function() {
                        btn.toggleClass('expanded', target.is(':visible'));
                    });
                });



                $(document).on('click', '.btn-view-work', function() {
                    openWorkViewer({
                        id: $(this).data('id'),
                        artist: $(this).data('artist'),
                        title: $(this).data('title'),
                        description: $(this).data('description'),
                        image: $(this).data('image'),
                        active: $(this).data('active'),
                        order: $(this).data('order')
                    });
                });

                modalArtist.on('hidden.bs.modal', function() {
                    resetArtistForm();
                });

                modalPortfolio.on('hidden.bs.modal', function() {
                    resetPortfolioForm();
                });

                modalPortfolioGallery.on('hidden.bs.modal', function() {
                    $('#portfolioGalleryContainer').html(
                        '<div class="portfolio-empty-box">No hay trabajos para mostrar.</div>');
                });
            });
        })();
    </script>
@endpush
