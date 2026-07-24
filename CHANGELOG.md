# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this projec
## [Unreleased]

### Milestone 1 — Core schema, models, contracts, CRUD
- 8 migrations: conversations, participants, messages, attachments, reactions, message_reads, user_status, blocks
- Eloquent models with polymorphic relations (morphTo/morphs)
- Contracts: TenantResolver, PresenceDriver with default implementations
- REST CRUD controllers for conversations and messages
- Feature tests: polymorphic CRUD across TestCustomer/TestAgent, tenant resolver, presence driver, migration
- Model factories for all tables

### Milestone 2 — Reverb broadcasting, tenant-aware channels, auth
- MessageSent and ConversationUpdated events (ShouldBroadcast)
- Tenant-prefixed channel naming via TenantResolver
- ChatChannel auth guard (participant must belong to conversation)
- Event::fake() tests for dispatch assertions
- Broadcasting auth and tenant channel tests
- docs/events-and-broadcasting.md, docs/multi-tenancy.md
