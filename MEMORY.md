# API Driver Core Memory
## Scope
- Package role: Abstraction (Core)
- Purpose: This package operates within the Abstraction (Core) layer of the APIs Hub SaaS hierarchy, providing core logic for all drivers.
- Dependency stance: Consumes `anibalealvarezs/api-client-skeleton` and serves all other drivers.
## Local working rules
- Consult `AGENTS.md` first for package-specific instructions.
- Use this `MEMORY.md` for repository-specific decisions, learnings, and follow-up notes.
- Use `D:\laragon\www\_shared\AGENTS.md` and `D:\laragon\www\_shared\MEMORY.md` for cross-repository protocols and workspace-wide learnings.
- Keep secrets, credentials, tokens, and private endpoints out of this file.
## Current notes
- Shared driver foundation for orchestration and normalization helpers.
- Metric profile templates and the aggregation strategy registry should remain the canonical shared abstraction for drivers and orchestrator-side index planning.
- Cache strategy improvements should keep channel-config lookup resilient and cache key generation deterministic, including recursive normalization of nested payloads.
- Shared Phase 2 (aggregation profiles) has started in this package with base artifacts: `Interfaces\AggregationProfileProviderInterface`, `Classes\AggregationProfileNormalizer`, and `Classes\AggregationProfileTemplates`, plus focused unit coverage under `tests/Unit/Classes`.
- Aggregation profile normalization now canonicalizes filter operator aliases (e.g., `=`, `==`, `!=`, `<>`) into planner-compatible tokens (`eq`, `neq`, `is_null`, `is_not_null`) to avoid false `missing_profile_capability` fallbacks.
- Canonical metric equivalence Phase A is now in place for read-only aggregation resolution: `Interfaces\CanonicalMetricDictionaryProviderInterface` and `Classes\CanonicalMetricDefinitionRegistry` (including legacy alias normalization like `purchase_roas -> roas_purchase`) with focused unit coverage in `tests/Unit/Classes/CanonicalMetricDefinitionRegistryTest.php`.
- `CanonicalMetricDefinitionRegistry` now also exposes input-resolution metadata (`canonical`, `legacy_alias`, `deprecation`) so downstream resolvers can keep backward compatibility while flagging ambiguous requests like `actions` as deprecated instead of treating them as canonical.
- **Aggregation Profiles**: Added support for `default_filters` in `AggregationProfileTemplates`, enabling drivers to specify mandatory platform-specific filters agnostically.
- **Identity Resolution**: Standardized the `getPlatformEntityIdField()` contract across all drivers to facilitate agnostic identity extraction from channel data.

