# FileIPlay Upload API v1

Create a personal token at **Profile settings → API & upload automation** (`/settings/api`). The full token appears when created. To view it later, open **Your personal tokens → View token**, enter your account password and click **Reveal token**. Newly created tokens have a Laravel-encrypted reveal copy; authentication still uses a SHA-256 hash. Plaintext tokens are never stored. Older tokens have no recoverable copy: revoke and replace them to enable later viewing. Keep it in a secret store or environment variable. Tokens expire after 30, 90 or 365 days and can be revoked individually or together. Current account and uploader permissions are checked on every request.

## Authentication and endpoints

Send `Authorization: Bearer YOUR_PERSONAL_TOKEN` over HTTPS. Tokens in query strings and browser sessions do not authenticate these endpoints. Torznab/Prowlarr/Sonarr/Radarr keys are separate and remain unchanged.

| Endpoint | Scope | Result |
| --- | --- | --- |
| `POST /api/v1/torrents` | `torrents:upload` | Publish a private torrent |
| `GET /api/v1/torrents/{id}` | `torrents:read` | Read safe torrent metadata |
| `GET /api/v1/categories` | `torrents:read` | List current category IDs and names |

The read scope does not grant tracker access or torrent-file download permission.

## Multipart upload fields

| Field | Requirement |
| --- | --- |
| `torrent` | Required valid BitTorrent v1 `.torrent`, up to 10 MiB. Pure v2 and hybrid torrents are currently unsupported. |
| `category_id` | Required existing FileIPlay category ID. Use the categories endpoint rather than guessing IDs. |
| `name` | Torrent release title, up to 255 characters. Required unless supported metadata supplies it. Existing release-name normalization applies. Concurrent uploads with the same title receive distinct URLs; URL suffixes stay within the database length limit. |
| `description` | Description, up to 100,000 characters. Required unless supported metadata supplies an overview. |
| `tmdbid`, `tmdb_type` | Optional positive TMDB ID; `tmdb_type` is required with it and must be `movie` or `tv`. |
| `tvdbid` | Optional positive TVDB series ID, resolved through TMDB. |
| `imdb_url` | Optional HTTPS IMDb title URL, such as `https://www.imdb.com/title/tt1234567/`. |
| `season`, `episode` | Optional TV numbers; season 0–999, episode 1–9999. Episode requires season. |
| `poster`, `background` | Optional HTTP/HTTPS artwork URLs. Manual values take priority. The upload API does not fetch user-supplied URLs. |
| `genre` | Optional comma/slash separated genre names, up to 1,000 characters. |
| `steamid` | Optional positive Steam app ID; existing enrichment applies. |
| `mediainfo` | Optional MediaInfo text, up to 100,000 characters. |
| `images[]` | Optional screenshots: up to 10 JPG/PNG/WebP files, up to 10 MiB each and 4096 × 2160 pixels. API screenshot encoding runs after publication using the existing image service; refresh the torrent page once processing completes. |
| `free`, `double`, `sticky`, `seedbox` | Optional `0`/`1` flags, requiring moderator permission when enabled. Existing automatic freeleech above 5 GiB is preserved. |
| `anon` | Optional `0`/`1`: show uploader identity / publish anonymously. Omit it to inherit your profile preference. Explicit `0` overrides an anonymous default. Owners and moderators retain identity access. |

Never submit `owner`, `info_hash`, `announce`, `passkey`, `file_name`, `approved`, `external`, `seeders`, `leechers` or `recommended`. The server controls these fields. Public/external torrent publishing is not supported by this private Upload API.

Success and read responses include the `anonymous` boolean and never include the uploader identity, tracker passkey or announce URL. Anonymous uploads hide the uploader from other members on site displays and public profile upload lists. This does not hide identifying text in torrent content, descriptions, comments or independent activity.

Metadata fills missing fields and preserves manual values. Failed lookups return a warning when manual details are sufficient; otherwise validation fails. Your explicit category remains authoritative and movie/TV mismatches are rejected. Release years populate existing title-library records. Season/episode fields are stored without changing existing integration searches.

## Responses

Success is HTTP **201**, with a `Location` header pointing to the metadata endpoint:

```json
{"data":{"id":123,"name":"Example.Movie.2026.1080p","info_hash":"PUBLISHED_INFO_HASH","category_id":11,"anonymous":false,"size":1000,"num_files":1,"tmdbid":null,"tmdb_type":null,"imdbid":null,"tvdbid":null,"season":null,"episode":null,"created_at":"2026-10-08T12:00:00+00:00","url":"https://fileiplay.org/torrents/123/example-movie-2026-1080p"},"meta":{"warnings":[]}}
```

Errors always use safe JSON, even without an Accept header:

```json
{"error":{"code":"validation_failed","message":"The supplied fields are invalid.","fields":{"category_id":["The selected category id is invalid."]}}}
```

| HTTP | Error code | Meaning |
| --- | --- | --- |
| 401 | `unauthenticated` | Missing, invalid, expired or revoked token |
| 403 | `forbidden` | Missing scope, uploader permission or active account access |
| 404 | `not_found` | Resource unavailable, including deleted torrents |
| 409 | `duplicate_torrent` | The normalized published info hash already exists, including soft-deleted torrents |
| 413 | `request_too_large` | Request exceeds the applicable size limit |
| 422 | `validation_failed` | Invalid file, metadata, unsupported field or privileged setting |
| 429 | `rate_limited` | Retry after the `Retry-After` header interval |
| 500 | `request_failed` | Internal failure; no internal details are returned |

Do not retry validation failures without correcting the request. After a network timeout, retry the same torrent; a 409 may indicate the first request succeeded. Check the published torrent page/history. Database uniqueness prevents another upload or bonus award for the same info hash.

## Limits and seeding

Default limits: 6 upload attempts/minute per user **and** token, 60 reads/minute per user and token, and 120 API requests/minute per source IP. Failed authenticated upload attempts count toward the limit. Application request cap: 120 MiB; PHP, proxy and web-server limits can impose a smaller cap. HTTP limits applied before Laravel may have infrastructure-generated response bodies.

FileIPlay replaces announce URLs, removes extra trackers and sets `private=1`. This can change the info hash. **Download the published torrent from its FileIPlay page and seed that torrent.** API JSON never contains tracker passkeys. Store tokens securely, revoke unused tokens and avoid recording Authorization headers or private torrent bytes in logs.

## cURL

```bash
export FILEIPLAY_API_TOKEN='YOUR_PERSONAL_TOKEN'
curl --fail-with-body 'https://fileiplay.org/api/v1/torrents' \
  -H "Authorization: Bearer $FILEIPLAY_API_TOKEN" \
  -H 'Accept: application/json' \
  -F 'torrent=@/path/to/release.torrent' \
  -F 'name=Example.Movie.2026.1080p' \
  -F 'description=Release description' \
  -F 'category_id=11'
```

For metadata-assisted uploading, add `-F 'tmdbid=123' -F 'tmdb_type=movie'`; omit name or description only if provider data can supply them. Discover the correct category first with `GET /api/v1/categories`.

## Python (requests)

Install `requests` in your uploader environment if needed; it is not a FileIPlay server dependency.

```python
import os
import requests

headers = {
    "Authorization": "Bearer " + os.environ["FILEIPLAY_API_TOKEN"],
    "Accept": "application/json",
}
with open("release.torrent", "rb") as torrent_file:
    response = requests.post(
        "https://fileiplay.org/api/v1/torrents",
        headers=headers,
        files={"torrent": ("release.torrent", torrent_file, "application/x-bittorrent")},
        data={"name": "Example.Movie.2026.1080p", "description": "Release description", "category_id": 11},
        timeout=(10, 60),
    )
result = response.json()
if response.status_code == 201:
    print("Published:", result["data"]["url"])
elif response.status_code == 409:
    print("Already published. Check your upload history.")
else:
    print("Upload failed:", result["error"]["code"], result["error"]["message"])
```

## Seedbox publishing

Publishing is always explicitly requested. A background job can publish a selected completed torrent from a configured seedbox only where the provider supports safe source export and registration against its existing directory. No large payload files pass through Laravel. Providers without these capabilities report an unsupported-operation status; creating torrents from arbitrary remote files is unavailable. Existing torrents are never stopped or deleted by publishing.

See `/settings/api/history` for attempts and `/settings/api/publishing` for publishing status when enabled. Administrators can view sanitized attempts at `/admin/upload-history`. Older uploads are not automatically backfilled.

Provider protocol references: [ruTorrent source export](https://github.com/Novik/ruTorrent/blob/master/plugins/source/action.php), [registration](https://github.com/Novik/ruTorrent/blob/master/php/addtorrent.php), and [HTTPRPC](https://github.com/Novik/ruTorrent/blob/master/plugins/httprpc/action.php). Actual provider versions and enabled plugins must be verified in staging.

Seedbox publication forms include the anonymous choice. That choice is saved when you authorize publishing and is preserved across background execution and retries, even if you later change your profile default. Existing client torrents keep their current state; automatic verification/start applies only to torrents recorded as newly registered by the publication. A duplicate client registration is reported for manual seeding.

Seedbox publishing is enabled on FileIPlay with a dedicated background worker after read-only provider/source checks. Select an owned seedbox, load eligible completed content, enter publication details and explicitly authorize the selected torrent. Optionally select registration/seeding. No existing content is published automatically. If a provider is unsupported or background publishing is disabled, normal website/API uploads still work. Safe queue readiness is enforced for publication requests, retries and queued client operations.
