# n8n Server Setup

## Purpose
This n8n layer is intended to:
- discover RSS feeds and subject websites
- import blog drafts into Laravel
- import professor leads into Laravel
- trigger email outreach workflows

## Laravel endpoints
Use the shared header:
- `X-N8N-Token: <N8N_SHARED_TOKEN>`

Endpoints:
- `GET /api/v1/automation/bootstrap`
- `POST /api/v1/automation/sources/sync`
- `POST /api/v1/automation/blog/import`
- `POST /api/v1/automation/professor-leads/import`

## Example payloads
### Source sync
```json
{
  "subject": "ssc cgl",
  "sources": [
    {
      "name": "Example Jobs Feed",
      "source_type": "rss",
      "base_url": "https://example.com",
      "rss_url": "https://example.com/feed.xml",
      "discovery_query": "ssc cgl jobs rss"
    }
  ]
}
```

### Blog import
```json
{
  "posts": [
    {
      "title": "SSC CGL 2026 notification update",
      "category": "Vacancy",
      "subject": "ssc cgl",
      "excerpt": "Latest update from official and news sources.",
      "content": "<p>Draft content...</p>",
      "source_name": "Example Jobs Feed",
      "source_url": "https://example.com/ssc-cgl-2026-update",
      "import_channel": "n8n",
      "status": "draft"
    }
  ]
}
```

### Professor lead import
```json
{
  "subject": "computer science",
  "leads": [
    {
      "name": "Dr. A Example",
      "email": "prof@example.edu",
      "institution": "Example University",
      "department": "Computer Science",
      "designation": "Professor",
      "profile_url": "https://example.edu/faculty/a-example",
      "source_name": "Example University Faculty Directory",
      "source_url": "https://example.edu/faculty"
    }
  ]
}
```

## Recommended workflows
1. Subject discovery
- search the web for a subject
- extract site URLs and RSS feeds
- send results to `/api/v1/automation/sources/sync`

2. RSS / site blog import
- poll active RSS feeds or scrape article pages
- normalize title/content/source/date/category
- send to `/api/v1/automation/blog/import`

3. Professor lead search
- search university/faculty pages or educator profiles
- extract name/email/department/institution/profile URL
- send to `/api/v1/automation/professor-leads/import`

4. Outreach
- read approved leads from Laravel admin exports or separate endpoint
- send throttled email sequences
- keep suppression / unsubscribe handling in Laravel or your mail platform

## Server start
```bash
docker compose -f deploy/n8n/docker-compose.n8n.yml up -d
```
