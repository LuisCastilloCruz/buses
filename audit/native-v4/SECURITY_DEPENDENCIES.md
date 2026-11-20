# Auditoria de dependencias V4

Ejecutada el 2026-11-20 con Composer 2.7.1: `composer --no-plugins --no-scripts audit --locked --format=json`. No se instalaron ni actualizaron paquetes.

113 avisos en 23 paquetes; 15 paquetes abandonados. Es una coincidencia de versiones con avisos publicados, no una prueba de explotacion ni una certificacion. La aptitud para produccion esta BLOQUEADA por avisos criticos pendientes de mitigacion y validacion.

| Paquete | Severidad publicada | Identificador | Fuente |
|---|---|---|---|
| binarytorch/larecipe | critical | CVE-2025-53833 | [Aviso](https://github.com/advisories/GHSA-jv7x-xhv2-p5v2) |
| dompdf/dompdf | medium | CVE-2026-59943 | [Aviso](https://github.com/advisories/GHSA-j8qw-6jw8-r297) |
| dompdf/dompdf | medium | CVE-2026-59942 | [Aviso](https://github.com/advisories/GHSA-f5gf-2cj8-52g2) |
| dompdf/dompdf | medium | CVE-2026-59941 | [Aviso](https://github.com/advisories/GHSA-8hg6-c449-896m) |
| dompdf/dompdf | medium | CVE-2026-56722 | [Aviso](https://github.com/advisories/GHSA-cx96-42px-69fm) |
| dompdf/dompdf | low | CVE-2026-55555 | [Aviso](https://github.com/advisories/GHSA-7x2p-4jvh-6384) |
| dompdf/dompdf | low | CVE-2026-55554 | [Aviso](https://github.com/advisories/GHSA-wvh6-f5jh-8gw4) |
| dompdf/dompdf | critical | CVE-2021-3902 | [Aviso](https://github.com/advisories/GHSA-3vjh-xrhf-v9xh) |
| dompdf/dompdf | critical | CVE-2021-3838 | [Aviso](https://github.com/advisories/GHSA-577p-7j7h-2jgf) |
| dompdf/dompdf | medium | CVE-2023-50262 | [Aviso](https://nvd.nist.gov/vuln/detail/CVE-2023-50262) |
| dompdf/dompdf | critical | CVE-2023-23924 | [Aviso](https://github.com/advisories/GHSA-3cw5-7cxw-v5qg) |
| dompdf/dompdf | high | CVE-2022-41343 | [Aviso](https://github.com/advisories/GHSA-6x28-7h8c-chx4) |
| dompdf/dompdf | medium | CVE-2022-2400 | [Aviso](https://github.com/advisories/GHSA-5qj8-6xxj-hp9h) |
| dompdf/dompdf | medium | CVE-2022-0085 | [Aviso](https://github.com/advisories/GHSA-pf6p-25r2-fx45) |
| dompdf/dompdf | critical | CVE-2022-28368 | [Aviso](https://github.com/advisories/GHSA-x752-qjv4-c4hc) |
| guzzlehttp/guzzle | high | CVE-2026-69246 | [Aviso](https://github.com/advisories/GHSA-v5mv-p594-2x33) |
| guzzlehttp/guzzle | medium | CVE-2026-69245 | [Aviso](https://github.com/advisories/GHSA-f7vp-7xgx-4w4r) |
| guzzlehttp/guzzle | medium | CVE-2026-67354 | [Aviso](https://github.com/advisories/GHSA-h95v-h523-3mw8) |
| guzzlehttp/guzzle | medium | CVE-2026-67355 | [Aviso](https://github.com/advisories/GHSA-wm3w-8rrp-j577) |
| guzzlehttp/guzzle | medium | CVE-2026-67353 | [Aviso](https://github.com/advisories/GHSA-f283-ghqc-fg79) |
| guzzlehttp/guzzle | medium | CVE-2026-59883 | [Aviso](https://github.com/advisories/GHSA-g446-98w2-8p5w) |
| guzzlehttp/guzzle | medium | CVE-2026-67339 | [Aviso](https://github.com/advisories/GHSA-94pj-82f3-465w) |
| guzzlehttp/guzzle | medium | CVE-2026-55767 | [Aviso](https://github.com/guzzle/guzzle/security/advisories/GHSA-cwxw-98qj-8qjx) |
| guzzlehttp/guzzle | medium | CVE-2026-55568 | [Aviso](https://github.com/guzzle/guzzle/security/advisories/GHSA-wpwq-4j6v-78m3) |
| guzzlehttp/psr7 | medium | CVE-2026-59882 | [Aviso](https://github.com/advisories/GHSA-c2w2-prh8-qm98) |
| guzzlehttp/psr7 | medium | CVE-2026-55766 | [Aviso](https://github.com/guzzle/psr7/security/advisories/GHSA-vm85-hxw5-5432) |
| guzzlehttp/psr7 | medium | CVE-2026-49214 | [Aviso](https://github.com/guzzle/psr7/security/advisories/GHSA-hq7v-mx3g-29hw) |
| guzzlehttp/psr7 | medium | CVE-2026-48998 | [Aviso](https://github.com/guzzle/psr7/security/advisories/GHSA-34xg-wgjx-8xph) |
| laravel/framework | low | CVE-2026-128083 | [Aviso](https://github.com/advisories/GHSA-63hm-g96r-2m2q) |
| laravel/framework | critical | CVE-2026-114509 | [Aviso](https://github.com/advisories/GHSA-f9x3-7vq9-cph6) |
| laravel/framework | low | CVE-2026-102279 | [Aviso](https://github.com/advisories/GHSA-jh5r-qr3c-85q8) |
| laravel/framework | medium | PKSA-m5cs-t1y6-qpcs | [Aviso](https://github.com/advisories/GHSA-crmm-hgp2-wgrp) |
| laravel/framework | high | PKSA-3r5d-mb8f-1qw9 | [Aviso](https://github.com/advisories/GHSA-5vg9-5847-vvmq) |
| laravel/framework | medium | CVE-2025-27515 | [Aviso](https://github.com/advisories/GHSA-78fx-h6xr-vch4) |
| laravel/framework | high | CVE-2024-52301 | [Aviso](https://github.com/advisories/GHSA-gv7v-rgg6-548h) |
| laravel/framework | critical | CVE-2019-9081 | [Aviso](https://github.com/advisories/GHSA-pfg4-p438-p874) |
| laravel/framework | high | CVE-2020-19316 | [Aviso](https://github.com/advisories/GHSA-w2pm-r78h-4m7v) |
| laravel/framework | high | CVE-2020-24941 | [Aviso](https://github.com/advisories/GHSA-w68r-5p45-5rqp) |
| laravel/framework | medium | CVE-2021-43808 | [Aviso](https://github.com/laravel/framework/security/advisories/GHSA-66hf-2p6w-jqfw) |
| laravel/framework | medium | PKSA-nrj3-r2wg-yt8b | [Aviso](https://blog.laravel.com/laravel-cookie-security-releases) |
| league/commonmark | medium | PKSA-m2dq-1fhr-29b1 | [Aviso](https://github.com/advisories/GHSA-97jj-33gv-5xf9) |
| league/commonmark | high | PKSA-zyf5-hrxv-hrd7 | [Aviso](https://github.com/advisories/GHSA-8rr7-cvq3-gmfh) |
| league/commonmark | high | PKSA-nv44-1b4d-6gjg | [Aviso](https://github.com/advisories/GHSA-jjv6-8j6v-6j52) |
| league/commonmark | high | PKSA-9q1p-3s19-bp1q | [Aviso](https://github.com/advisories/GHSA-j8pm-gj4c-rq4x) |
| league/commonmark | high | PKSA-1q6p-sqkj-8mmj | [Aviso](https://github.com/advisories/GHSA-jfm3-95jq-q3rf) |
| league/commonmark | high | PKSA-mc58-w91n-f5gv | [Aviso](https://github.com/advisories/GHSA-g2gp-3wwq-f4ph) |
| league/commonmark | high | CVE-2026-71488 | [Aviso](https://github.com/advisories/GHSA-2q4p-g7hv-5rgv) |
| league/commonmark | medium | CVE-2026-71478 | [Aviso](https://github.com/advisories/GHSA-29pj-957v-52mc) |
| league/commonmark | medium | CVE-2025-46734 | [Aviso](https://github.com/advisories/GHSA-3527-qv2q-pfvx) |
| league/commonmark | high | PKSA-fndg-qryc-dyc9 | [Aviso](https://github.com/advisories/GHSA-c2pc-g5qf-rfrf) |
| league/flysystem | low | CVE-2026-102601 | [Aviso](https://github.com/advisories/GHSA-cxf4-7mrp-vvpr) |
| maatwebsite/excel | high | CVE-2026-84374 | [Aviso](https://github.com/advisories/GHSA-c7r6-vx3h-w5g2) |
| nesbot/carbon | medium | CVE-2025-22145 | [Aviso](https://github.com/advisories/GHSA-j3f9-p6hm-5w6q) |
| phenx/php-svg-lib | critical | PKSA-qsyb-3psh-f1t3 | [Aviso](https://github.com/advisories/GHSA-97m3-52wr-xvv2) |
| phenx/php-svg-lib | medium | CVE-2024-25117 | [Aviso](https://github.com/advisories/GHSA-f3qr-qr4x-j273) |
| phenx/php-svg-lib | medium | CVE-2023-50251 | [Aviso](https://github.com/advisories/GHSA-ff5x-7qg5-vwf2) |
| phpoffice/phpspreadsheet | medium | CVE-2026-108360 | [Aviso](https://github.com/advisories/GHSA-2phh-27fw-q4gc) |
| phpoffice/phpspreadsheet | high | CVE-2026-108359 | [Aviso](https://github.com/advisories/GHSA-j2w7-387v-m2rq) |
| phpoffice/phpspreadsheet | high | CVE-2026-108358 | [Aviso](https://github.com/advisories/GHSA-3x94-m842-937c) |
| phpoffice/phpspreadsheet | high | CVE-2026-59933 | [Aviso](https://github.com/advisories/GHSA-xh5m-36r6-47m3) |
| phpoffice/phpspreadsheet | high | CVE-2026-59932 | [Aviso](https://github.com/advisories/GHSA-2mrg-gjxq-2gvr) |
| phpoffice/phpspreadsheet | high | CVE-2026-59931 | [Aviso](https://github.com/advisories/GHSA-6hq5-7373-42rg) |
| phpoffice/phpspreadsheet | critical | CVE-2026-45034 | [Aviso](https://github.com/advisories/GHSA-87m4-826x-3crx) |
| phpoffice/phpspreadsheet | high | CVE-2026-40902 | [Aviso](https://github.com/advisories/GHSA-7c6m-4442-2x6m) |
| phpoffice/phpspreadsheet | high | CVE-2026-40863 | [Aviso](https://github.com/advisories/GHSA-84wq-86v6-x5j6) |
| phpoffice/phpspreadsheet | critical | CVE-2026-34084 | [Aviso](https://github.com/advisories/GHSA-q4q6-r8wh-5cgh) |
| phpoffice/phpspreadsheet | medium | CVE-2026-40296 | [Aviso](https://github.com/advisories/GHSA-hrmw-qprp-wgmc) |
| phpoffice/phpspreadsheet | medium | CVE-2026-35453 | [Aviso](https://github.com/advisories/GHSA-6wpp-88cp-7q68) |
| phpoffice/phpspreadsheet | high | CVE-2025-54370 | [Aviso](https://github.com/PHPOffice/PhpSpreadsheet/security/advisories/GHSA-rx7m-68vc-ppxh) |
| phpoffice/phpspreadsheet | medium | CVE-2025-23210 | [Aviso](https://github.com/advisories/GHSA-r57h-547h-w24f) |
| phpoffice/phpspreadsheet | medium | CVE-2025-22131 | [Aviso](https://github.com/advisories/GHSA-79xx-vf93-p7cx) |
| phpoffice/phpspreadsheet | medium | CVE-2024-56412 | [Aviso](https://github.com/advisories/GHSA-q9jv-mm3r-j47r) |
| phpoffice/phpspreadsheet | medium | CVE-2024-56411 | [Aviso](https://github.com/advisories/GHSA-hwcp-2h35-p66w) |
| phpoffice/phpspreadsheet | medium | CVE-2024-56410 | [Aviso](https://github.com/advisories/GHSA-wv23-996v-q229) |
| phpoffice/phpspreadsheet | high | CVE-2024-56409 | [Aviso](https://github.com/advisories/GHSA-j2xg-cjcx-4677) |
| phpoffice/phpspreadsheet | high | CVE-2024-56366 | [Aviso](https://github.com/advisories/GHSA-c6fv-7vh8-2rhr) |
| phpoffice/phpspreadsheet | high | CVE-2024-56365 | [Aviso](https://github.com/advisories/GHSA-jmpx-686v-c3wx) |
| phpoffice/phpspreadsheet | high | CVE-2024-56408 | [Aviso](https://github.com/advisories/GHSA-x88g-h956-m5xg) |
| phpoffice/phpspreadsheet | high | CVE-2024-48917 | [Aviso](https://github.com/advisories/GHSA-7cc9-j4mv-vcjp) |
| phpoffice/phpspreadsheet | high | CVE-2024-47873 | [Aviso](https://github.com/advisories/GHSA-jw4x-v69f-hh5w) |
| phpoffice/phpspreadsheet | high | CVE-2024-45293 | [Aviso](https://github.com/advisories/GHSA-6hwr-6v2f-3m88) |
| phpoffice/phpspreadsheet | medium | CVE-2024-45292 | [Aviso](https://github.com/advisories/GHSA-r8w8-74ww-j4wh) |
| phpoffice/phpspreadsheet | medium | CVE-2024-45291 | [Aviso](https://github.com/advisories/GHSA-w9xv-qf98-ccq4) |
| phpoffice/phpspreadsheet | high | CVE-2024-45290 | [Aviso](https://github.com/advisories/GHSA-5gpr-w2p5-6m37) |
| phpoffice/phpspreadsheet | medium | CVE-2024-45060 | [Aviso](https://github.com/advisories/GHSA-v66g-p9x6-v98p) |
| phpoffice/phpspreadsheet | high | CVE-2024-45048 | [Aviso](https://github.com/advisories/GHSA-ghg6-32f9-2jp7) |
| phpoffice/phpspreadsheet | medium | CVE-2024-45046 | [Aviso](https://github.com/advisories/GHSA-wgmf-q9vr-vww6) |
| phpseclib/phpseclib | sin clasificar | PKSA-h7cz-q7zq-mdkt | [Aviso](https://github.com/phpseclib/phpseclib/security/advisories/GHSA-w3p9-g4p2-h3rr) |
| phpseclib/phpseclib | medium | CVE-2026-84308 | [Aviso](https://github.com/advisories/GHSA-q97c-8qh3-fpc6) |
| phpseclib/phpseclib | medium | CVE-2026-55599 | [Aviso](https://github.com/advisories/GHSA-m557-wrgg-6rp4) |
| phpseclib/phpseclib | high | CVE-2026-44167 | [Aviso](https://github.com/advisories/GHSA-3qpq-r242-jqj7) |
| phpseclib/phpseclib | low | CVE-2026-40194 | [Aviso](https://github.com/advisories/GHSA-r854-jrxh-36qx) |
| phpseclib/phpseclib | high | CVE-2026-32935 | [Aviso](https://github.com/advisories/GHSA-94g3-g5v7-q4jg) |
| phpunit/phpunit | high | CVE-2026-24765 | [Aviso](https://github.com/sebastianbergmann/phpunit/security/advisories/GHSA-vvj3-c3rp-c85p) |
| psy/psysh | medium | CVE-2026-25129 | [Aviso](https://github.com/advisories/GHSA-4486-gxhx-5mg7) |
| robrichards/xmlseclibs | high | CVE-2026-32313 | [Aviso](https://github.com/advisories/GHSA-4v26-v6cg-g6f9) |
| robrichards/xmlseclibs | medium | CVE-2025-66578 | [Aviso](https://github.com/advisories/GHSA-c4cc-x928-vjw9) |
| setasign/fpdi | medium | CVE-2026-45802 | [Aviso](https://github.com/advisories/GHSA-2mgw-7q6p-8grg) |
| setasign/fpdi | medium | CVE-2025-54869 | [Aviso](https://github.com/advisories/GHSA-jxhh-4648-vpp3) |
| symfony/dom-crawler | low | CVE-2026-45071 | [Aviso](https://symfony.com/cve-2026-45071) |
| symfony/http-foundation | sin clasificar | CVE-2026-118514 | [Aviso](https://symfony.com/cve-2026-118514) |
| symfony/http-foundation | sin clasificar | PKSA-b2gg-vk1g-6d5q | [Aviso](https://github.com/symfony/symfony/security/advisories/GHSA-5mx8-mwrp-5693) |
| symfony/http-foundation | high | CVE-2025-64500 | [Aviso](https://symfony.com/blog/cve-2025-64500-incorrect-parsing-of-path-info-can-lead-to-limited-authorization-bypass) |
| symfony/http-foundation | low | CVE-2024-50345 | [Aviso](https://symfony.com/cve-2024-50345) |
| symfony/http-kernel | sin clasificar | CVE-2026-123339 | [Aviso](https://github.com/symfony/symfony/security/advisories/GHSA-wg5q-m2ww-f3c9) |
| symfony/mime | medium | CVE-2026-45070 | [Aviso](https://symfony.com/cve-2026-45070) |
| symfony/mime | high | CVE-2026-45067 | [Aviso](https://symfony.com/cve-2026-45067) |
| symfony/polyfill-intl-idn | low | CVE-2026-46644 | [Aviso](https://symfony.com/cve-2026-46644) |
| symfony/process | medium | CVE-2026-24739 | [Aviso](https://github.com/advisories/GHSA-r39x-jcww-82v6) |
| symfony/process | high | CVE-2024-51736 | [Aviso](https://symfony.com/cve-2024-51736) |
| symfony/routing | sin clasificar | CVE-2026-118516 | [Aviso](https://symfony.com/cve-2026-118516) |
| symfony/routing | medium | CVE-2026-48784 | [Aviso](https://symfony.com/cve-2026-48784) |
| symfony/routing | medium | CVE-2026-45065 | [Aviso](https://symfony.com/cve-2026-45065) |

## Contencion aplicada
Servidor HTTP ligado a 127.0.0.1, MySQL privado en 13319, bases ficticias, credenciales nuevas, funciones de salida PHP bloqueadas en el proceso web, providers desactivados, navegador de pruebas bloquea red externa, adjuntos nuevos fuera de public y cifrados. Esto reduce exposicion del laboratorio; NO corrige todas las vulnerabilidades heredadas.

## Modernizacion pendiente
Crear otra rama/worktree de compatibilidad; inventariar extensiones PHP y consumidores de paquetes; elegir versiones soportadas; actualizar por etapas con suites de facturacion, XML/firma, PDF, POS, Hyn, colas y modulos. No ejecutar composer update global sobre esta entrega ni sobre clientes. Requiere validacion de documentos tributarios en entorno autorizado. No abrir el laboratorio a Internet ni configurar un tunel para Meta antes de resolver los riesgos aplicables.
