# Archivos V4 respecto de la base V3

Inventario anterior al commit de documentacion; incluye archivos existentes modificados y nuevos del worktree. No incluye runtime privado.

```text
M	.github/workflows/ai-reception-standalone-tests.yml
M	app/Exceptions/Handler.php
M	app/Http/Controllers/System/ClientController.php
A	audit/native-v4/PROGRESS.md
A	audit/native-v4/TEST_RESULTS.md
A	audit/native-v4/screenshots/01-original-tattoo-dashboard.png
A	audit/native-v4/screenshots/02-tattoo-calendar.png
A	audit/native-v4/screenshots/03-tattoo-availability.png
A	audit/native-v4/screenshots/04-tattoo-week.png
A	audit/native-v4/screenshots/05-original-tattoo-appointments.png
A	audit/native-v4/screenshots/06-original-dental-dashboard.png
A	audit/native-v4/screenshots/07-odontologia-admin.png
A	audit/native-v4/screenshots/08-dental-agent.png
A	audit/native-v4/screenshots/09-dental-calendar.png
A	audit/native-v4/screenshots/10-original-commerce-dashboard.png
A	audit/native-v4/screenshots/11-original-pos.png
A	audit/native-v4/screenshots/12-superadmin-clients.png
A	audit/native-v4/screenshots/13-superadmin-odontologia-preset.png
A	audit/native-v4/screenshots/14-menu-completo-comercio.png
A	audit/native-v4/screenshots/14-menu-completo-dental.png
A	audit/native-v4/screenshots/14-menu-completo-tattoo.png
A	audit/native-v4/screenshots/15-service-policy.png
A	audit/native-v4/screenshots/16-month-calendar.png
A	audit/native-v4/screenshots/17-protected-clinical.png
A	audit/native-v4/screenshots/18-clinical-mobile.png
A	audit/native-v4/screenshots/19-fourth-company-dashboard.png
A	database/migrations/2026_11_22_000001_register_clinical_permission.php
A	database/migrations/2026_11_22_000002_create_reception_queue.php
A	database/migrations/2026_11_22_000003_create_reception_plan_controls.php
A	database/migrations/2026_11_22_000004_create_reception_channel_registry.php
A	database/migrations/tenant/2026_11_22_000001_create_reception_usage_ledger.php
A	database/migrations/tenant/2026_11_22_000002_create_protected_dental_records.php
A	database/migrations/tenant/2026_11_22_000003_create_reception_booking_drafts.php
A	database/migrations/tenant/2026_11_22_000004_add_reminder_processing.php
A	database/migrations/tenant/2026_11_22_000005_create_clinical_attachments.php
A	database/migrations/tenant/2026_11_22_000006_create_reception_inbound_messages.php
M	modules/AiReception/Agenda/BookingService.php
A	modules/AiReception/Console/QueueReminders.php
A	modules/AiReception/Jobs/ProcessInbound.php
A	modules/AiReception/Jobs/ProcessReminder.php
M	modules/AiReception/Native/Access.php
A	modules/AiReception/Native/AutomationController.php
A	modules/AiReception/Native/BookingProposals.php
M	modules/AiReception/Native/Catalog.php
A	modules/AiReception/Native/ChannelOwnership.php
A	modules/AiReception/Native/ChannelSettings.php
M	modules/AiReception/Native/Controller.php
A	modules/AiReception/Native/DomainRegistry.php
M	modules/AiReception/Native/EngineFactory.php
A	modules/AiReception/Native/ProfessionalQuota.php
A	modules/AiReception/Native/ProposalController.php
M	modules/AiReception/Native/Provider.php
A	modules/AiReception/Native/ReminderProcessor.php
A	modules/AiReception/Native/ReminderSchedule.php
A	modules/AiReception/Native/SubscriptionPolicy.php
A	modules/AiReception/Native/SystemController.php
A	modules/AiReception/Native/UsageBudget.php
A	modules/AiReception/Native/WebhookController.php
A	modules/AiReception/ProvidersApi/ResponsesClient.php
A	modules/AiReception/ProvidersApi/TranscriptionClient.php
A	modules/AiReception/ProvidersApi/WhatsAppClient.php
M	modules/AiReception/Resources/views/agenda.blade.php
A	modules/AiReception/Resources/views/automations.blade.php
M	modules/AiReception/Resources/views/conversations.blade.php
A	modules/AiReception/Resources/views/system.blade.php
M	modules/AiReception/Routes/native.php
M	modules/AiReception/Services/MetaWebhook.php
A	modules/AiReception/Tests/AudioContractsTest.php
M	modules/AiReception/Tests/MetaWebhookMockTest.php
A	modules/AiReception/Tests/ProviderContractsTest.php
M	modules/Odontologia/Http/Controllers/AdminController.php
A	modules/Odontologia/Http/Controllers/ClinicalController.php
A	modules/Odontologia/Resources/views/clinical.blade.php
M	modules/Odontologia/Resources/views/index.blade.php
M	modules/Odontologia/Routes/web.php
A	modules/Odontologia/Services/ClinicalAttachments.php
A	modules/Odontologia/Services/ClinicalRecords.php
M	modules/TattooInbox/Http/Controllers/MetaWebhookController.php
M	modules/TattooInbox/Providers/TattooInboxServiceProvider.php
A	modules/TattooInbox/Services/CatalogMigrationCompatibility.php
M	modules/TattooInbox/Services/MetaChannelRegistryService.php
M	native-lab/README.md
A	native-lab/ReservedDatabase.php
A	native-lab/artisan.php
A	native-lab/attachment-tests.php
M	native-lab/bootstrap.php
M	native-lab/browser-settings.mjs
A	native-lab/browser-v4.mjs
M	native-lab/browser.mjs
M	native-lab/check-publication.py
M	native-lab/finish-fixtures.php
A	native-lab/fourth-company-test.py
M	native-lab/http-smoke.py
A	native-lab/inbound-tests.php
M	native-lab/init.php
M	native-lab/migrate-modules.php
M	native-lab/migrate-original.php
M	native-lab/mysql-tests.php
A	native-lab/plan-http-tests.py
A	native-lab/proposal-tests.php
A	native-lab/reminder-tests.php
A	native-lab/reminder-verify.php
A	native-lab/reserve-fourth.php
M	native-lab/router.php
M	native-lab/run-tests.ps1
M	native-lab/schema-bootstrap.php
M	native-lab/seed.php
M	native-lab/start.ps1
M	native-lab/test-bootstrap.php
A	native-lab/v4-persistence-tests.php
A	native-lab/webhook-fixture.php
A	native-lab/webhook-http-tests.py
M	public/js/aireception-native.js
M	resources/views/system/clients/index.blade.php
M	resources/views/tenant/layouts/partials/native_reception.blade.php
audit/native-v4/BACKUP_RECOVERY.md
audit/native-v4/FUNCTION_MATRIX.md
audit/native-v4/INFORME_V4.md
audit/native-v4/MANUAL_AIRECEPTION.md
audit/native-v4/MANUAL_ODONTOLOGIA.md
audit/native-v4/MANUAL_SUPERADMIN.md
audit/native-v4/MANUAL_TATTOO.md
audit/native-v4/OPENAI_SETUP.md
audit/native-v4/OPERATING_COSTS.md
audit/native-v4/REMINDERS.md
audit/native-v4/SAFE_DEPLOYMENT.md
audit/native-v4/SECURITY_DEPENDENCIES.md
audit/native-v4/SECURITY_UPGRADE.md
audit/native-v4/WHATSAPP_SETUP.md
audit/native-v4/screenshots/20-agent-settings.png
audit/native-v4/screenshots/21-whatsapp-channels.png
audit/native-v4/screenshots/22-inbox.png
audit/native-v4/screenshots/23-reception-usage.png
audit/native-v4/screenshots/24-automations.png
database/migrations/2026_11_22_000005_create_reception_model_rates.php
database/migrations/2026_11_22_000006_create_reception_delivery_receipts.php
database/migrations/tenant/2026_11_22_000007_link_reception_contacts.php
database/migrations/tenant/2026_11_22_000008_protect_received_conversation_content.php
database/migrations/tenant/2026_11_22_000009_create_agent_configuration.php
database/migrations/tenant/2026_11_22_000010_create_reception_agent_turns.php
database/migrations/tenant/2026_11_22_000011_create_reception_reminder_policy.php
modules/AiReception/Console/ReminderTick.php
modules/AiReception/Native/AgentController.php
modules/AiReception/Native/AgentSettings.php
modules/AiReception/Native/BudgetedResponses.php
modules/AiReception/Native/ContactLinker.php
modules/AiReception/Native/ConversationController.php
modules/AiReception/Native/ConversationCoordinator.php
modules/AiReception/Native/InboxController.php
modules/AiReception/Native/ReceptionTools.php
modules/AiReception/Native/ReminderPolicy.php
modules/AiReception/Resources/views/agent-settings.blade.php
modules/AiReception/Resources/views/channels.blade.php
modules/AiReception/Resources/views/inbox.blade.php
modules/AiReception/Resources/views/overview.blade.php
native-lab/agent-http-tests.py
native-lab/budgeted-provider-tests.php
native-lab/contact-link-tests.php
native-lab/conversation-tests.php
native-lab/reminder-policy-tests.php

```

Migraciones son aditivas en database/migrations y database/migrations/tenant; no ejecutar down destructivo. Ver informe para compatibilidad historica.
