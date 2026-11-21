# Auditor?a de dependencias V5

Auditor?a ejecutada mediante Composer --no-plugins --no-scripts audit --locked --format=json. No se actualizaron paquetes.

Resultado: 113 avisos, 23 paquetes afectados, 15 abandonados.

Todos los avisos siguientes siguen abiertos en el monolito. La contenci?n no es una correcci?n de la dependencia. El gateway no importa paquetes PHP ni sirve archivos del monolito. Laravel permanece ligado a loopback; queda prohibido publicarlo mediante proxy o t?nel.

| Paquete / versi?n | Aviso | Severidad | Superficie y prioridad | Fuente |
|---|---|---|---|---|
| binarytorch/larecipe v2.7.0 | CVE-2025-53833 | critical | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-jv7x-xhv2-p5v2) |
| dompdf/dompdf v0.8.6 | CVE-2026-59943 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-j8qw-6jw8-r297) |
| dompdf/dompdf v0.8.6 | CVE-2026-59942 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-f5gf-2cj8-52g2) |
| dompdf/dompdf v0.8.6 | CVE-2026-59941 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-8hg6-c449-896m) |
| dompdf/dompdf v0.8.6 | CVE-2026-56722 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-cx96-42px-69fm) |
| dompdf/dompdf v0.8.6 | CVE-2026-55555 | low | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-7x2p-4jvh-6384) |
| dompdf/dompdf v0.8.6 | CVE-2026-55554 | low | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-wvh6-f5jh-8gw4) |
| dompdf/dompdf v0.8.6 | CVE-2021-3902 | critical | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-3vjh-xrhf-v9xh) |
| dompdf/dompdf v0.8.6 | CVE-2021-3838 | critical | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-577p-7j7h-2jgf) |
| dompdf/dompdf v0.8.6 | CVE-2023-50262 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://nvd.nist.gov/vuln/detail/CVE-2023-50262) |
| dompdf/dompdf v0.8.6 | CVE-2023-23924 | critical | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-3cw5-7cxw-v5qg) |
| dompdf/dompdf v0.8.6 | CVE-2022-41343 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-6x28-7h8c-chx4) |
| dompdf/dompdf v0.8.6 | CVE-2022-2400 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-5qj8-6xxj-hp9h) |
| dompdf/dompdf v0.8.6 | CVE-2022-0085 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-pf6p-25r2-fx45) |
| dompdf/dompdf v0.8.6 | CVE-2022-28368 | critical | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-x752-qjv4-c4hc) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-69246 | high | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/advisories/GHSA-v5mv-p594-2x33) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-69245 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/advisories/GHSA-f7vp-7xgx-4w4r) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-67354 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/advisories/GHSA-h95v-h523-3mw8) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-67355 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/advisories/GHSA-wm3w-8rrp-j577) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-67353 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/advisories/GHSA-f283-ghqc-fg79) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-59883 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/advisories/GHSA-g446-98w2-8p5w) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-67339 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/advisories/GHSA-94pj-82f3-465w) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-55767 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/guzzle/guzzle/security/advisories/GHSA-cwxw-98qj-8qjx) |
| guzzlehttp/guzzle 6.5.8 | CVE-2026-55568 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/guzzle/guzzle/security/advisories/GHSA-wpwq-4j6v-78m3) |
| guzzlehttp/psr7 1.9.1 | CVE-2026-59882 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/advisories/GHSA-c2w2-prh8-qm98) |
| guzzlehttp/psr7 1.9.1 | CVE-2026-55766 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/guzzle/psr7/security/advisories/GHSA-vm85-hxw5-5432) |
| guzzlehttp/psr7 1.9.1 | CVE-2026-49214 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/guzzle/psr7/security/advisories/GHSA-hq7v-mx3g-29hw) |
| guzzlehttp/psr7 1.9.1 | CVE-2026-48998 | medium | P0: clientes HTTP; salida externa trasladada a gateway, sin redirecciones | [aviso](https://github.com/guzzle/psr7/security/advisories/GHSA-34xg-wgjx-8xph) |
| laravel/framework v5.7.29 | CVE-2026-128083 | low | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-63hm-g96r-2m2q) |
| laravel/framework v5.7.29 | CVE-2026-114509 | critical | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-f9x3-7vq9-cph6) |
| laravel/framework v5.7.29 | CVE-2026-102279 | low | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-jh5r-qr3c-85q8) |
| laravel/framework v5.7.29 | PKSA-m5cs-t1y6-qpcs | medium | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-crmm-hgp2-wgrp) |
| laravel/framework v5.7.29 | PKSA-3r5d-mb8f-1qw9 | high | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-5vg9-5847-vvmq) |
| laravel/framework v5.7.29 | CVE-2025-27515 | medium | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-78fx-h6xr-vch4) |
| laravel/framework v5.7.29 | CVE-2024-52301 | high | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-gv7v-rgg6-548h) |
| laravel/framework v5.7.29 | CVE-2019-9081 | critical | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-pfg4-p438-p874) |
| laravel/framework v5.7.29 | CVE-2020-19316 | high | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-w2pm-r78h-4m7v) |
| laravel/framework v5.7.29 | CVE-2020-24941 | high | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/advisories/GHSA-w68r-5p45-5rqp) |
| laravel/framework v5.7.29 | CVE-2021-43808 | medium | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/laravel/framework/security/advisories/GHSA-66hf-2p6w-jqfw) |
| laravel/framework v5.7.29 | PKSA-nrj3-r2wg-yt8b | medium | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://blog.laravel.com/laravel-cookie-security-releases) |
| league/commonmark 1.6.7 | PKSA-m2dq-1fhr-29b1 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-97jj-33gv-5xf9) |
| league/commonmark 1.6.7 | PKSA-zyf5-hrxv-hrd7 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-8rr7-cvq3-gmfh) |
| league/commonmark 1.6.7 | PKSA-nv44-1b4d-6gjg | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-jjv6-8j6v-6j52) |
| league/commonmark 1.6.7 | PKSA-9q1p-3s19-bp1q | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-j8pm-gj4c-rq4x) |
| league/commonmark 1.6.7 | PKSA-1q6p-sqkj-8mmj | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-jfm3-95jq-q3rf) |
| league/commonmark 1.6.7 | PKSA-mc58-w91n-f5gv | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-g2gp-3wwq-f4ph) |
| league/commonmark 1.6.7 | CVE-2026-71488 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-2q4p-g7hv-5rgv) |
| league/commonmark 1.6.7 | CVE-2026-71478 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-29pj-957v-52mc) |
| league/commonmark 1.6.7 | CVE-2025-46734 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-3527-qv2q-pfvx) |
| league/commonmark 1.6.7 | PKSA-fndg-qryc-dyc9 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-c2pc-g5qf-rfrf) |
| league/flysystem 1.1.10 | CVE-2026-102601 | low | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-cxf4-7mrp-vvpr) |
| maatwebsite/excel 3.1.25 | CVE-2026-84374 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-c7r6-vx3h-w5g2) |
| nesbot/carbon 1.39.1 | CVE-2025-22145 | medium | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-j3f9-p6hm-5w6q) |
| phenx/php-svg-lib 0.3.4 | PKSA-qsyb-3psh-f1t3 | critical | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-97m3-52wr-xvv2) |
| phenx/php-svg-lib 0.3.4 | CVE-2024-25117 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-f3qr-qr4x-j273) |
| phenx/php-svg-lib 0.3.4 | CVE-2023-50251 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-ff5x-7qg5-vwf2) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-108360 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-2phh-27fw-q4gc) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-108359 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-j2w7-387v-m2rq) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-108358 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-3x94-m842-937c) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-59933 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-xh5m-36r6-47m3) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-59932 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-2mrg-gjxq-2gvr) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-59931 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-6hq5-7373-42rg) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-45034 | critical | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-87m4-826x-3crx) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-40902 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-7c6m-4442-2x6m) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-40863 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-84wq-86v6-x5j6) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-34084 | critical | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-q4q6-r8wh-5cgh) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-40296 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-hrmw-qprp-wgmc) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2026-35453 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-6wpp-88cp-7q68) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2025-54370 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/PHPOffice/PhpSpreadsheet/security/advisories/GHSA-rx7m-68vc-ppxh) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2025-23210 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-r57h-547h-w24f) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2025-22131 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-79xx-vf93-p7cx) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-56412 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-q9jv-mm3r-j47r) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-56411 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-hwcp-2h35-p66w) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-56410 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-wv23-996v-q229) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-56409 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-j2xg-cjcx-4677) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-56366 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-c6fv-7vh8-2rhr) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-56365 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-jmpx-686v-c3wx) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-56408 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-x88g-h956-m5xg) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-48917 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-7cc9-j4mv-vcjp) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-47873 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-jw4x-v69f-hh5w) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-45293 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-6hwr-6v2f-3m88) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-45292 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-r8w8-74ww-j4wh) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-45291 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-w9xv-qf98-ccq4) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-45290 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-5gpr-w2p5-6m37) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-45060 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-v66g-p9x6-v98p) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-45048 | high | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-ghg6-32f9-2jp7) |
| phpoffice/phpspreadsheet 1.29.0 | CVE-2024-45046 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-wgmf-q9vr-vww6) |
| phpseclib/phpseclib 2.0.47 | PKSA-h7cz-q7zq-mdkt | None | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/phpseclib/phpseclib/security/advisories/GHSA-w3p9-g4p2-h3rr) |
| phpseclib/phpseclib 2.0.47 | CVE-2026-84308 | medium | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-q97c-8qh3-fpc6) |
| phpseclib/phpseclib 2.0.47 | CVE-2026-55599 | medium | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-m557-wrgg-6rp4) |
| phpseclib/phpseclib 2.0.47 | CVE-2026-44167 | high | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-3qpq-r242-jqj7) |
| phpseclib/phpseclib 2.0.47 | CVE-2026-40194 | low | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-r854-jrxh-36qx) |
| phpseclib/phpseclib 2.0.47 | CVE-2026-32935 | high | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-94g3-g5v7-q4jg) |
| phpunit/phpunit 7.5.20 | CVE-2026-24765 | high | P1: desarrollo/consola; fuera del servidor p?blico, no expuesto por gateway | [aviso](https://github.com/sebastianbergmann/phpunit/security/advisories/GHSA-vvj3-c3rp-c85p) |
| psy/psysh v0.9.12 | CVE-2026-25129 | medium | P1: desarrollo/consola; fuera del servidor p?blico, no expuesto por gateway | [aviso](https://github.com/advisories/GHSA-4486-gxhx-5mg7) |
| robrichards/xmlseclibs 3.0.4 | CVE-2026-32313 | high | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-4v26-v6cg-g6f9) |
| robrichards/xmlseclibs 3.0.4 | CVE-2025-66578 | medium | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-c4cc-x928-vjw9) |
| setasign/fpdi v2.6.0 | CVE-2026-45802 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-2mgw-7q6p-8grg) |
| setasign/fpdi v2.6.0 | CVE-2025-54869 | medium | P1: documentos/importaci?n/render; no aceptar archivos del canal en estos parsers | [aviso](https://github.com/advisories/GHSA-jxhh-4648-vpp3) |
| symfony/dom-crawler v5.4.39 | CVE-2026-45071 | low | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://symfony.com/cve-2026-45071) |
| symfony/http-foundation v4.4.49 | CVE-2026-118514 | None | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://symfony.com/cve-2026-118514) |
| symfony/http-foundation v4.4.49 | PKSA-b2gg-vk1g-6d5q | None | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/symfony/symfony/security/advisories/GHSA-5mx8-mwrp-5693) |
| symfony/http-foundation v4.4.49 | CVE-2025-64500 | high | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://symfony.com/blog/cve-2025-64500-incorrect-parsing-of-path-info-can-lead-to-limited-authorization-bypass) |
| symfony/http-foundation v4.4.49 | CVE-2024-50345 | low | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://symfony.com/cve-2024-50345) |
| symfony/http-kernel v4.4.51 | CVE-2026-123339 | None | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://github.com/symfony/symfony/security/advisories/GHSA-wg5q-m2ww-f3c9) |
| symfony/mime v5.4.39 | CVE-2026-45070 | medium | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://symfony.com/cve-2026-45070) |
| symfony/mime v5.4.39 | CVE-2026-45067 | high | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://symfony.com/cve-2026-45067) |
| symfony/polyfill-intl-idn v1.29.0 | CVE-2026-46644 | low | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://symfony.com/cve-2026-46644) |
| symfony/process v4.4.44 | CVE-2026-24739 | medium | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://github.com/advisories/GHSA-r39x-jcww-82v6) |
| symfony/process v4.4.44 | CVE-2024-51736 | high | P1: dependencia transitoria; no accesible desde gateway, requiere actualizaci?n y regresi?n del consumidor legacy | [aviso](https://symfony.com/cve-2024-51736) |
| symfony/routing v4.4.44 | CVE-2026-118516 | None | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://symfony.com/cve-2026-118516) |
| symfony/routing v4.4.44 | CVE-2026-48784 | medium | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://symfony.com/cve-2026-48784) |
| symfony/routing v4.4.44 | CVE-2026-45065 | medium | P0: rutas, sesi?n, autorizaci?n o documentaci?n; bloquear exposici?n HTTP legacy | [aviso](https://symfony.com/cve-2026-45065) |

## Alcance de la evaluaci?n

Clasificaci?n conservadora por componente y rutas de integraci?n; no se afirma explotaci?n comprobada ni ausencia de otras rutas vulnerables. No se actualizaron globalmente Laravel/PHP ni se modific? producci?n. El listado exacto permite revisar cada aviso antes de una modernizaci?n independiente.

## Aislamiento V5

Gateway Node sin dependencias npm: HMAC por cliente, nonce persistente y caducidad, tenant y canal permitidos, almacenamiento SQLite cifrado, destinos fijos, l?mite de cuerpos y conexi?n, sin redirecciones, sin panel administrativo. La configuraci?n de sandbox requiere aprobaci?n, claves propias y destinatarios permitidos. El servidor suministrado solo escucha 127.0.0.1. No se abri? webhook p?blico.

Pendiente antes de exposici?n externa: terminaci?n TLS/mTLS, revisi?n de versi?n parcheada del runtime y decoder, ingress p?blico separado, pruebas sandbox firmadas, pol?ticas de retenci?n, l?mites operacionales y revisi?n independiente. El gateway interno no autoriza a exponer Laravel.