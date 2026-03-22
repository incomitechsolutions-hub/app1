# Email Processes

## Purpose
Define the email communication model for inquiry notifications, autoresponders, workflow alerts, and later operational communications.

## Core Rules
- emails should be template-based
- sends should be logged
- failures should be visible
- email should be event-driven

## Current Phase Emails

### New Inquiry Notification
Trigger:
- inquiry stored successfully

Recipients:
- internal sales mailbox
- optional market/team inbox

### Inquiry Autoresponder
Trigger:
- inquiry received

Purpose:
- confirm receipt
- reinforce trust

### System Alerts
Examples:
- failed queue job
- failed CRM sync
- failed notification dispatch

## Template Requirements
Each template should define:
- key
- subject
- HTML/text body
- variables
- locale readiness

## Logging Requirements
Track:
- template key
- recipient
- related entity
- send result
- timestamp
- error details if applicable

## Future Operational Emails
Later:
- trainer invitation
- trainer reminder
- session confirmation
- feedback request
