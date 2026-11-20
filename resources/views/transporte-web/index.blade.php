//multifacturaperunew\resources\views\transporte-web
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transportes Interprovinciales</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root{
            --bg:#f6f8fc;
            --card:#ffffff;
            --line:#dbe3ef;
            --text:#17324d;
            --muted:#6b7c93;
            --primary:#2563eb;
            --primary-hover:#1d4ed8;
            --success:#16a34a;
            --warning:#f59e0b;
            --danger:#dc2626;
            --shadow:0 10px 30px rgba(15, 23, 42, .08);
            --radius:18px;
            --radius-sm:12px;
        }

        *{box-sizing:border-box}
        body{
            margin:0;
            font-family:Inter, Arial, sans-serif;
            background:var(--bg);
            color:var(--text);
        }

        .container{
            width:min(1260px, calc(100% - 32px));
            margin:0 auto;
        }

        .topbar{
            background:#fff;
            border-bottom:1px solid var(--line);
            position:sticky;
            top:0;
            z-index:20;
        }

        .topbar-inner{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:20px;
            padding:18px 0;
        }

        .brand{
            display:flex;
            align-items:center;
            gap:14px;
            text-decoration:none;
            color:inherit;
        }

        .brand-badge{
            width:48px;
            height:48px;
            border-radius:16px;
            display:grid;
            place-items:center;
            background:linear-gradient(135deg, #3b82f6, #2563eb);
            color:#fff;
            font-size:22px;
            box-shadow:var(--shadow);
        }

        .brand-title{
            font-size:22px;
            font-weight:800;
        }

        .brand-sub{
            font-size:13px;
            color:var(--muted);
            margin-top:3px;
        }

        .hero{
            padding:32px 0 18px;
        }

        .hero-card,
        .search-card,
        .results-list,
        .seat-panel{
            background:var(--card);
            border:1px solid var(--line);
            border-radius:24px;
            box-shadow:var(--shadow);
        }

        .hero-card{
            padding:30px;
            margin-bottom:22px;
        }

        .hero-card h1{
            margin:0 0 12px;
            font-size:40px;
            line-height:1.1;
        }

        .hero-card p{
            margin:0;
            color:var(--muted);
            line-height:1.7;
            font-size:16px;
            max-width:760px;
        }

        .search-card{
            padding:20px;
        }

        .search-grid{
            display:grid;
            grid-template-columns:1.1fr 1.1fr 1fr 220px;
            gap:14px;
            align-items:end;
        }

        .field{
            display:flex;
            flex-direction:column;
            gap:8px;
        }

        .field label{
            font-size:14px;
            font-weight:700;
        }

        .field input,
        .field select{
            width:100%;
            border:1px solid var(--line);
            background:#fff;
            color:var(--text);
            border-radius:14px;
            padding:14px 16px;
            font-size:15px;
            outline:none;
        }

        .field input:focus,
        .field select:focus{
            border-color:#93c5fd;
            box-shadow:0 0 0 4px rgba(37,99,235,.12);
        }

        .btn{
            border:0;
            cursor:pointer;
            border-radius:999px;
            padding:14px 18px;
            font-size:15px;
            font-weight:700;
            transition:.2s ease;
        }

        .btn:hover{transform:translateY(-1px)}
        .btn-primary{
            background:var(--primary);
            color:#fff;
        }
        .btn-primary:hover{
            background:var(--primary-hover);
        }

        .notice{
            margin-top:14px;
            padding:13px 15px;
            background:#f8fbff;
            border:1px solid var(--line);
            border-radius:14px;
            color:var(--muted);
            font-size:14px;
        }

        .section{
            padding:26px 0 34px;
        }

        .section h2{
            margin:0 0 18px;
            font-size:30px;
        }

        .results-shell{
            display:grid;
            grid-template-columns:1.08fr .92fr;
            gap:18px;
        }

        .results-list,
        .seat-panel{
            padding:18px;
        }

        .trip-card{
            border:1px solid var(--line);
            border-radius:18px;
            padding:18px;
            display:grid;
            grid-template-columns:1.2fr .8fr 180px;
            gap:14px;
            align-items:center;
            background:#fcfdff;
            margin-bottom:14px;
        }

        .trip-route{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            align-items:center;
            font-size:19px;
            font-weight:800;
        }

        .trip-meta{
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            margin-top:10px;
        }

        .pill{
            background:#eef4ff;
            color:#31517c;
            border:1px solid #d7e5ff;
            border-radius:999px;
            padding:8px 11px;
            font-size:12px;
            font-weight:700;
        }

        .price{
            font-size:28px;
            font-weight:800;
            color:var(--primary);
        }

        .empty{
            min-height:200px;
            display:grid;
            place-items:center;
            text-align:center;
            color:var(--muted);
            border:1px dashed var(--line);
            border-radius:18px;
            padding:20px;
        }

        .seat-head{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:14px;
            flex-wrap:wrap;
            margin-bottom:12px;
        }

        .deck-toggle{
            display:flex;
            gap:8px;
        }

        .toggle-btn{
            border:1px solid var(--line);
            background:#fff;
            color:var(--text);
            border-radius:999px;
            padding:10px 14px;
            font-weight:700;
            cursor:pointer;
        }

        .toggle-btn.active{
            background:var(--primary);
            color:#fff;
            border-color:var(--primary);
        }

        .legend{
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            margin-bottom:14px;
        }

        .legend-item{
            display:flex;
            align-items:center;
            gap:8px;
            color:var(--muted);
            font-size:13px;
        }

        .dot{
            width:14px;
            height:14px;
            border-radius:50%;
            display:inline-block;
        }

        .dot.free{background:#cbd5e1}
        .dot.busy{background:var(--danger)}
        .dot.reserved{background:var(--warning)}
        .dot.selected{background:var(--success)}

        .bus-map{
            border:1px solid var(--line);
            border-radius:20px;
            background:#f8fbff;
            overflow:auto;
            padding:18px;
        }

        .bus-stage{
            position:relative;
            min-width:760px;
            height:620px;
            border:1px dashed #c8d6eb;
            border-radius:22px;
            background:#fff;
        }

        .driver-zone{
            position:absolute;
            left:16px;
            top:16px;
            width:120px;
            height:120px;
            border-radius:24px;
            border:1px solid var(--line);
            background:#eef4ff;
            display:grid;
            place-items:center;
            font-weight:700;
            color:var(--muted);
        }

        .seat{
            position:absolute;
            width:54px;
            height:54px;
            border-radius:16px;
            border:1px solid #d6e1f0;
            background:#e2e8f0;
            color:#1e293b;
            font-weight:800;
            display:grid;
            place-items:center;
            cursor:pointer;
            transition:.18s ease;
        }

        .seat:hover{
            transform:translateY(-1px) scale(1.02);
        }

        .seat.busy{
            background:#fecaca;
            border-color:#f87171;
            color:#991b1b;
            cursor:not-allowed;
        }

        .seat.reserved{
            background:#fde68a;
            border-color:#f59e0b;
            color:#92400e;
            cursor:not-allowed;
        }

        .seat.selected{
            background:#bbf7d0;
            border-color:#22c55e;
            color:#166534;
            box-shadow:0 10px 22px rgba(34,197,94,.18);
        }

        .summary{
            margin-top:14px;
            padding:16px;
            border:1px solid var(--line);
            border-radius:18px;
            background:#fcfdff;
        }

        .summary-grid{
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:12px;
            margin-top:12px;
        }

        .summary-item{
            border:1px solid var(--line);
            border-radius:14px;
            padding:12px;
            background:#fff;
        }

        .summary-item span{
            display:block;
            font-size:12px;
            color:var(--muted);
            margin-bottom:6px;
        }

        .summary-item strong{
            font-size:15px;
        }

        .footer{
            padding:30px 0 40px;
            color:var(--muted);
        }

        @media (max-width: 1100px){
            .search-grid,
            .results-shell,
            .trip-card{
                grid-template-columns:1fr;
            }
        }

        @media (max-width: 720px){
            .container{
                width:min(1260px, calc(100% - 18px));
            }
            .hero-card h1{
                font-size:30px;
            }
            .summary-grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a href="#" class="brand">
                <div class="brand-badge">🚌</div>
                <div>
                    <div class="brand-title">Sistema de Transportes</div>
                    <div class="brand-sub">Consulta de salidas y asientos en tiempo real</div>
                </div>
            </a>
        </div>
    </header>

    <main class="hero">
        <div class="container">
            <div class="hero-card">
                <h1>Consulta tus salidas disponibles y visualiza los asientos reales</h1>
                <p>
                    Esta página ya no usa datos demo. Consulta directamente tu base de datos Laravel:
                    programaciones, terminales, vehículo y estado real de asientos.
                </p>
            </div>

            <div class="search-card">
                <div class="search-grid">
                    <div class="field">
                        <label for="originSelect">Origen</label>
                        <select id="originSelect">
                            <option value="">Cargando...</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="destinationSelect">Destino</label>
                        <select id="destinationSelect">
                            <option value="">Cargando...</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="departureDate">Fecha de salida</label>
                        <input type="date" id="departureDate">
                    </div>

                    <div class="field">
                        <button class="btn btn-primary" id="searchBtn">Buscar disponibilidad</button>
                    </div>
                </div>

                <div class="notice" id="searchNotice">Cargando terminales...</div>
            </div>
        </div>
    </main>

    <section class="section">
        <div class="container">
            <h2>Salidas encontradas</h2>

            <div class="results-shell">
                <div class="results-list">
                    <div id="resultsContainer" class="empty">Busca una salida para ver resultados.</div>
                </div>

                <aside class="seat-panel">
                    <div class="seat-head">
                        <div>
                            <h3 style="margin:0 0 4px;">Mapa de asientos</h3>
                            <div style="color:var(--muted); font-size:14px;">Conectado al vehículo real</div>
                        </div>

                        <div class="deck-toggle">
                            <button class="toggle-btn active" data-floor="1">Piso 1</button>
                            <button class="toggle-btn" data-floor="2">Piso 2</button>
                        </div>
                    </div>

                    <div class="legend">
                        <div class="legend-item"><span class="dot free"></span> Disponible</div>
                        <div class="legend-item"><span class="dot busy"></span> Ocupado</div>
                        <div class="legend-item"><span class="dot reserved"></span> Reservado</div>
                        <div class="legend-item"><span class="dot selected"></span> Seleccionado</div>
                    </div>

                    <div class="bus-map">
                        <div class="bus-stage" id="busStage">
                            <div class="driver-zone">Conductor</div>
                        </div>
                    </div>

                    <div class="summary">
                        <strong>Resumen</strong>

                        <div class="summary-grid">
                            <div class="summary-item">
                                <span>Ruta</span>
                                <strong id="summaryRoute">Sin seleccionar</strong>
                            </div>

                            <div class="summary-item">
                                <span>Hora</span>
                                <strong id="summaryTime">--:--</strong>
                            </div>

                            <div class="summary-item">
                                <span>Asiento</span>
                                <strong id="summarySeat">Sin seleccionar</strong>
                            </div>

                            <div class="summary-item">
                                <span>Vehículo</span>
                                <strong id="summaryVehicle">Sin seleccionar</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            Sistema conectado a Laravel · Transportes públicos
        </div>
    </footer>

    <script>
        const API_BASE = '/transportes-public';
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const state = {
            currentFloor: 1,
            trips: [],
            seats: [],
            selectedTrip: null,
            selectedSeat: null,
            vehicle: null,
        };

        const $ = (selector) => document.querySelector(selector);
        const $$ = (selector) => Array.from(document.querySelectorAll(selector));

        function fillSelect(select, items, placeholder) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nombre;
                select.appendChild(option);
            });
        }

        async function loadTerminales() {
            const res = await fetch(`${API_BASE}/terminales`);
            const json = await res.json();

            if (!json.success) {
                throw new Error('No se pudieron cargar terminales');
            }

            fillSelect($('#originSelect'), json.data, 'Selecciona origen');
            fillSelect($('#destinationSelect'), json.data, 'Selecciona destino');

            $('#searchNotice').textContent = 'Terminales cargados correctamente.';
        }

        async function searchTrips() {
            const origen_id = $('#originSelect').value;
            const destino_id = $('#destinationSelect').value;
            const fecha_salida = $('#departureDate').value;

            if (!origen_id || !destino_id || !fecha_salida) {
                $('#searchNotice').textContent = 'Selecciona origen, destino y fecha.';
                return;
            }

            $('#searchBtn').disabled = true;
            $('#searchBtn').textContent = 'Buscando...';
            $('#searchNotice').textContent = 'Consultando programaciones reales...';

            try {
                const res = await fetch(`${API_BASE}/programaciones-disponibles`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ origen_id, destino_id, fecha_salida })
                });

                const json = await res.json();

                if (!json.success) {
                    throw new Error(json.message || 'No se pudo consultar programaciones');
                }

                state.trips = json.data || [];
                renderTrips();

                $('#searchNotice').textContent = `${state.trips.length} salida(s) encontradas.`;
            } catch (error) {
                console.error(error);
                $('#searchNotice').textContent = 'Error consultando programaciones.';
            } finally {
                $('#searchBtn').disabled = false;
                $('#searchBtn').textContent = 'Buscar disponibilidad';
            }
        }

        function renderTrips() {
            const container = $('#resultsContainer');

            if (!state.trips.length) {
                container.className = 'empty';
                container.innerHTML = 'No hay salidas para esa búsqueda.';
                return;
            }

            container.className = '';
            container.innerHTML = state.trips.map(trip => `
                <article class="trip-card">
                    <div>
                        <div class="trip-route">
                            <span>${trip.origen?.nombre || '-'}</span>
                            <span style="color:#94a3b8;">→</span>
                            <span>${trip.destino?.nombre || '-'}</span>
                        </div>

                        <div class="trip-meta">
                            <span class="pill">🕒 ${String(trip.hora_salida).slice(0,5)}</span>
                            <span class="pill">📅 ${trip.fecha_salida}</span>
                            <span class="pill">🚌 ${trip.vehiculo?.nombre || '-'}</span>
                            <span class="pill">💺 ${trip.vehiculo?.asientos || 0} asientos</span>
                            <span class="pill">🏢 ${trip.vehiculo?.placa || '-'}</span>
                        </div>
                    </div>

                    <div class="price">
                        ${trip.hora_salida ? String(trip.hora_salida).slice(0,5) : '--:--'}
                    </div>

                    <div>
                        <button class="btn btn-primary" onclick="selectTrip(${trip.id})">Ver asientos</button>
                    </div>
                </article>
            `).join('');
        }

        window.selectTrip = async function (id) {
            state.selectedTrip = state.trips.find(item => Number(item.id) === Number(id));
            state.selectedSeat = null;

            if (!state.selectedTrip) return;

            $('#summaryRoute').textContent = `${state.selectedTrip.origen?.nombre || '-'} → ${state.selectedTrip.destino?.nombre || '-'}`;
            $('#summaryTime').textContent = String(state.selectedTrip.hora_salida || '').slice(0,5);
            $('#summarySeat').textContent = 'Sin seleccionar';
            $('#summaryVehicle').textContent = state.selectedTrip.vehiculo?.nombre || '-';

            try {
                const res = await fetch(`${API_BASE}/asientos-disponibles`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        programacion_id: state.selectedTrip.id,
                        fecha_salida: state.selectedTrip.fecha_salida
                    })
                });

                const json = await res.json();

                if (!json.success) {
                    throw new Error(json.message || 'No se pudo consultar asientos');
                }

                state.vehicle = json.data.vehiculo;
                state.seats = json.data.asientos || [];

                renderSeats();
            } catch (error) {
                console.error(error);
                alert('Error cargando asientos');
            }
        }

        function renderSeats() {
            const busStage = $('#busStage');
            busStage.querySelectorAll('.seat').forEach(el => el.remove());
            busStage.querySelectorAll('.empty').forEach(el => el.remove());

            const seats = state.seats.filter(seat => Number(seat.piso) === Number(state.currentFloor));

            if (!seats.length) {
                const empty = document.createElement('div');
                empty.className = 'empty';
                empty.style.position = 'absolute';
                empty.style.inset = '150px 20px 20px';
                empty.textContent = 'No hay asientos para este piso.';
                busStage.appendChild(empty);
                return;
            }

            seats.forEach(seat => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `seat ${seat.estado} ${state.selectedSeat && state.selectedSeat.id === seat.id ? 'selected' : ''}`;
                btn.style.left = `${Number(seat.left)}px`;
                btn.style.top = `${Number(seat.top) + 120}px`;
                btn.textContent = seat.numero_asiento;

                if (seat.estado === 'busy' || seat.estado === 'reserved') {
                    btn.disabled = true;
                } else {
                    btn.addEventListener('click', () => {
                        state.selectedSeat = seat;
                        $('#summarySeat').textContent = `Asiento ${seat.numero_asiento}`;
                        renderSeats();
                    });
                }

                busStage.appendChild(btn);
            });
        }

        function bindEvents() {
            $('#searchBtn').addEventListener('click', searchTrips);

            $$('.toggle-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    $$('.toggle-btn').forEach(item => item.classList.remove('active'));
                    btn.classList.add('active');
                    state.currentFloor = Number(btn.dataset.floor);
                    renderSeats();
                });
            });
        }

        async function init() {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            $('#departureDate').value = `${yyyy}-${mm}-${dd}`;

            bindEvents();
            await loadTerminales();
        }

        init();
    </script>
</body>
</html>