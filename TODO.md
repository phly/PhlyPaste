# TODO

X Basic paste functionality:
  X Show and process form
  X View individual pastes
  X View paginated list of pastes
  X Show raw paste
  X Populate form from existing paste
X Second tier functionality:
  X Syntax highlighting
    X HTML Purifier for markdown
  X Captcha support
  X Split based on "##" sections
  X Move PhlyPaste\Mongo stuff into separate module
- Third tier functionality:
  X DB service
  - API
  - Display themes
  - Embed paste via JS
  - Array service (?)
  - Cacheable service decorator (?)
  - Dasbit plugin: !paste <id> -> return link and title/first line

API
---

- X-PhlyPaste-Token: <token>
  - Use API keys from configuration by default
- /paste/api/paste{/:paste}
  - POST to create
    - Return location of canonical HTML in 201 status
  - GET to get list; $page QUERY variable to determine page of results
    - Ideally, should include prev/next page relation links in results
    - each item would link to both canonical HTML and API version
  - GET with ID
    - return link to canonical HTML and first line
- See https://gist.github.com/1912431 for links example. Looks like this is a
  winner:

    {
        "links": [
            {"rel": "self", "href": "http://paste.local/paste/api/paste?page=2"},
            {"rel": "next", "href": "http://paste.local/paste/api/paste?page=3"},
            {"prev": "next", "href": "http://paste.local/paste/api/paste"},
        ]
    }

POST
^^^^

    http POST http://paste.local/paste/api/paste < data
    {
        "language": "php",
        "private": "false",
        "content": "..."
    }

    HTTP/1.0 201 Created
    Location: http://paste.local/paste/XYZ
    Content-Type: application/json

    {
        "links": [
            {"rel": "canonical", "href": "http://pages.local/paste/XYZ"},
            {"rel": "self", "href": "http://pages.local/paste/api/paste/XYZ"}
        ]
    }

GET (no hash)
^^^^^^^^^^^^^

    http GET http://paste.local/paste/api/paste

    HTTP/1.0 200 Created
    Content-Type: application/json

    {
        "links": [
            {"rel": "canonical", "href": "http://pages.local/paste"},
            {"rel": "self", "href": "http://pages.local/paste/api/paste"},
            {"rel": "first", "href": "http://pages.local/paste/api/paste"},
            {"rel": "last", "href": "http://pages.local/paste/api/paste?page=X"},
            {"rel": "next", "href": "http://pages.local/paste/api/paste?page=2"}
        ]
        "items": [
            [
                {"rel": "canonical", "href": "http://pages.local/paste/XYZ"},
                {"rel": "item", "href": "http://pages.local/paste/api/paste/XYZ"}
            ],
            /* ... */
        ]
    }

GET (with hash)
^^^^^^^^^^^^^^^

    http GET http://paste.local/paste/api/paste

    HTTP/1.0 200 Created
    Content-Type: application/json

    {
        "links": [
            {"rel": "canonical", "href": "http://pages.local/paste/XYZ"},
            {"rel": "self", "href": "http://pages.local/paste/api/paste/XYZ"},
            {"rel": "up", "href": "http://pages.local/paste/api/paste"}
        ],
        "title": "...",
        "language": "...",
        "timestamp": "...",
    }
