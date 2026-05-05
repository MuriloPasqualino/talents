# Compliance RHID — componentes

- `OverviewSection.vue`: visão geral (KPIs e atalhos).
- Demais áreas (marcações/aderência/espelho, banco de horas, justificativas, colaboradores) permanecem em `../Compliance.vue` até refatoração incremental; a lógica e os endpoints não mudam.

Horários da empresa foram movidos para `../Settings.vue` via `@/composables/useRhidPunchSchedule.js`.
