# Events and Broadcasting

## Events

### MessageSent

Dispatched when a message is created.

**Payload:**

- id
- conversation_id
- sender_type
- sender_id
- body
- created_at

**Channel:** chat.conversation.{id} or chat.{tenant}.conversation.{id}

### ConversationUpdated

Dispatched when a conversation is created or updated.
