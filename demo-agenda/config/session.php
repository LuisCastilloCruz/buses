<?php

return ['driver' => 'file', 'lifetime' => 120, 'expire_on_close' => false, 'encrypt' => true, 'files' => storage_path('framework/sessions'), 'connection' => null, 'table' => 'sessions', 'store' => null, 'lottery' => [2, 100], 'cookie' => 'aireception_lab_session', 'path' => '/', 'domain' => null, 'secure' => false, 'http_only' => true, 'same_site' => 'strict'];
