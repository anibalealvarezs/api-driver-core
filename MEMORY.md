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
