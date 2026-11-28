# V7.1 observed errors and corrections

1. PowerShell removed quotes from Node -e preflight: replaced with explicit .mjs file. Preflight then passed.
2. Fresh lab had no plan quota. First E2E made zero model calls; configured the fictional plan through original Superadmin HTTP (11 checks). E2E then passed 17 assertions each in dental and Tattoo with real Ollama, MySQL and simulated outbox.
3. Model asked for already known day: clarify_selection enum restricted to actually missing data; known date regression passed.
4. Model chose business_info for price despite description: descriptions clarified, same case still failed; narrowed explicit price requests to catalogue/knowledge/human tools. Repeated cases now choose correct catalogue. Expected answers unchanged.
5. Evaluation stopped on RAM guard after 19 complete conversations in iteration2. Opera had been closed normally with user authorization; Ollama itself used about3GB host RAM. Closed authorized idle desktop utilities and resumed completed checkpoints without replay.

Earlier failed transcripts retained. Global coherence thresholds and subjective naturalness are not approved merely from these corrections.
