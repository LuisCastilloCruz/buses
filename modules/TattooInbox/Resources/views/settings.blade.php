@extends('tenant.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title mb-0">Configuración Tattoo Inbox</h2>
                </header>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('tenant.tattoo-inbox.settings.update') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-3">Datos del tatuador</h4>

                                <div class="form-group">
                                    <label>Nombre comercial</label>
                                    <input type="text" name="studio_name" class="form-control" value="{{ old('studio_name', $settings->studio_name) }}">
                                </div>

                                <div class="form-group">
                                    <label>Nombre del artista</label>
                                    <input type="text" name="artist_name" class="form-control" value="{{ old('artist_name', $settings->artist_name) }}">
                                </div>

                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone) }}">
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" name="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email) }}">
                                </div>

                                <div class="form-group">
                                    <label>Distrito</label>
                                    <input type="text" name="district" class="form-control" value="{{ old('district', $settings->district) }}">
                                </div>

                                <div class="form-group">
                                    <label>Dirección</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $settings->address) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h4 class="mb-3">Conexión Meta</h4>

                                <div class="form-group">
                                    <label>Verify Token</label>
                                    <input type="text" name="meta_verify_token" class="form-control" value="{{ old('meta_verify_token', $settings->meta_verify_token) }}">
                                </div>

                                <div class="form-group">
                                    <label>Graph Version</label>
                                    <input type="text" name="meta_graph_version" class="form-control" value="{{ old('meta_graph_version', $settings->meta_graph_version) }}">
                                </div>

                                <div class="form-group">
                                    <label>Meta Page Access Token</label>
                                    <textarea name="meta_page_access_token" class="form-control" rows="3">{{ old('meta_page_access_token', $settings->meta_page_access_token) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>WhatsApp Access Token</label>
                                    <textarea name="whatsapp_access_token" class="form-control" rows="3">{{ old('whatsapp_access_token', $settings->whatsapp_access_token) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>WhatsApp Phone Number ID</label>
                                    <input type="text" name="whatsapp_phone_number_id" class="form-control" value="{{ old('whatsapp_phone_number_id', $settings->whatsapp_phone_number_id) }}">
                                </div>

                                <div class="form-group">
                                    <label>Instagram Page ID</label>
                                    <input type="text" name="instagram_page_id" class="form-control" value="{{ old('instagram_page_id', $settings->instagram_page_id) }}">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-3">Comportamiento del bot</h4>

                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="bot_enabled" value="1" {{ $settings->bot_enabled ? 'checked' : '' }}>
                                        Bot activado
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="auto_quote_enabled" value="1" {{ $settings->auto_quote_enabled ? 'checked' : '' }}>
                                        Cotización automática
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="human_takeover_enabled" value="1" {{ $settings->human_takeover_enabled ? 'checked' : '' }}>
                                        Permitir takeover humano
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label>Moneda</label>
                                    <input type="text" name="currency" class="form-control" value="{{ old('currency', $settings->currency) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h4 class="mb-3">Prompt y estilo</h4>

                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="openai_enabled" value="1" {{ $settings->openai_enabled ? 'checked' : '' }}>
                                        Usar OpenAI para responder clientes
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="openai_vision_enabled" value="1" {{ $settings->openai_vision_enabled ? 'checked' : '' }}>
                                        Analizar imagenes/referencias para cotizar
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label>OpenAI API Key</label>
                                    <textarea name="openai_api_key" class="form-control" rows="2">{{ old('openai_api_key', $settings->openai_api_key) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Modelo OpenAI</label>
                                    <input type="text" name="openai_model" class="form-control" value="{{ old('openai_model', $settings->openai_model ?: config('tattooinbox.ai.model', 'gpt-4.1-mini')) }}">
                                </div>

                                <div class="form-group">
                                    <label>Detalle de vision</label>
                                    <select name="openai_vision_detail" class="form-control">
                                        @foreach(['auto' => 'Auto', 'low' => 'Bajo', 'high' => 'Alto'] as $value => $label)
                                            <option value="{{ $value }}" {{ old('openai_vision_detail', $settings->openai_vision_detail ?: 'auto') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Tono del asistente</label>
                                    <textarea name="assistant_tone" class="form-control" rows="3">{{ old('assistant_tone', $settings->assistant_tone) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Prompt principal</label>
                                    <textarea name="assistant_prompt" class="form-control" rows="6">{{ old('assistant_prompt', $settings->assistant_prompt) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Instrucciones de reserva</label>
                                    <textarea name="booking_instructions" class="form-control" rows="4">{{ old('booking_instructions', $settings->booking_instructions) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Instrucciones post cuidado</label>
                                    <textarea name="aftercare_instructions" class="form-control" rows="4">{{ old('aftercare_instructions', $settings->aftercare_instructions) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                Guardar configuración
                            </button>
                        </div>
                    </form>

                    <hr>

                    <h4 class="mb-3">Agregar regla / plantilla</h4>

                    <form method="POST" action="{{ route('tenant.tattoo-inbox.settings.rules.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <select name="rule_type" class="form-control">
                                        <option value="quick_reply">Quick Reply</option>
                                        <option value="prompt_rule">Prompt Rule</option>
                                        <option value="blocked_topic">Blocked Topic</option>
                                        <option value="pricing_rule">Pricing Rule</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Clave</label>
                                    <input type="text" name="rule_key" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Valor</label>
                                    <textarea name="rule_value" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <input type="text" name="description" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>Activo</label><br>
                                    <input type="checkbox" name="active" value="1" checked>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">Agregar regla</button>
                    </form>

                    <hr>

                    <h4>Reglas actuales</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Clave</th>
                                    <th>Valor</th>
                                    <th>Descripción</th>
                                    <th>Activo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quickReplies as $rule)
                                    <tr>
                                        <td>{{ $rule->id }}</td>
                                        <td>{{ $rule->rule_type }}</td>
                                        <td>{{ $rule->rule_key }}</td>
                                        <td>{{ $rule->rule_value }}</td>
                                        <td>{{ $rule->description }}</td>
                                        <td>{{ $rule->active ? 'Sí' : 'No' }}</td>
                                    </tr>
                                @endforeach

                                @foreach($promptRules as $rule)
                                    <tr>
                                        <td>{{ $rule->id }}</td>
                                        <td>{{ $rule->rule_type }}</td>
                                        <td>{{ $rule->rule_key }}</td>
                                        <td>{{ $rule->rule_value }}</td>
                                        <td>{{ $rule->description }}</td>
                                        <td>{{ $rule->active ? 'Sí' : 'No' }}</td>
                                    </tr>
                                @endforeach

                                @foreach($blockedTopics as $rule)
                                    <tr>
                                        <td>{{ $rule->id }}</td>
                                        <td>{{ $rule->rule_type }}</td>
                                        <td>{{ $rule->rule_key }}</td>
                                        <td>{{ $rule->rule_value }}</td>
                                        <td>{{ $rule->description }}</td>
                                        <td>{{ $rule->active ? 'Sí' : 'No' }}</td>
                                    </tr>
                                @endforeach

                                @foreach($pricingRules as $rule)
                                    <tr>
                                        <td>{{ $rule->id }}</td>
                                        <td>{{ $rule->rule_type }}</td>
                                        <td>{{ $rule->rule_key }}</td>
                                        <td>{{ $rule->rule_value }}</td>
                                        <td>{{ $rule->description }}</td>
                                        <td>{{ $rule->active ? 'Sí' : 'No' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </section>
        </div>
    </div>
</div>
@endsection
