<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

const APP_NAME = 'Lig Flow';
const DATA_DIR = __DIR__ . '/data';
const DB_FILE = DATA_DIR . '/callflow.sqlite';
const IMPORT_DIR = __DIR__ . '/uploads/imports';
const DB_SCHEMA_VERSION = 24;
const TERMS_VERSION = '2026-08-12.1';

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0775, true);
}
if (!is_dir(DATA_DIR . '/sessions')) {
    mkdir(DATA_DIR . '/sessions', 0775, true);
}
if (!is_dir(__DIR__ . '/uploads/avatars')) {
    mkdir(__DIR__ . '/uploads/avatars', 0775, true);
}
if (!is_dir(IMPORT_DIR)) {
    mkdir(IMPORT_DIR, 0775, true);
}
session_save_path(DATA_DIR . '/sessions');
session_start();
load_env_file(__DIR__ . '/.env');
require_once __DIR__ . '/billing_domain.php';
require_once __DIR__ . '/asterisk_recording.php';
$termsContentFile = __DIR__ . '/terms_content.php';
if (is_file($termsContentFile)) {
    require_once $termsContentFile;
} else {
    define('EMBEDDED_TERMS_CONTENT_GZIP_BASE64', 'H4sIAAAAAAACCq1bzZIcN3K++ykQe7Ijmj0zpCRK8phhmqQV3OCKI1HyHdOF7gG3utBbqOpl8A38Cj6tYg8b2gieaF18dL+Yv0z8JaqqhzyYF870dAGJ/Pnyy0yUUkpdN/aoNq32/l9+N5h+7x80bjPuTTf87sk/qPjv+vDk2g+963ZPTv/VDnavlR5G3dr3+vS301/dt9cX8c/q6uHF5dcXDy8ffnV9cahW+Dezf3C0XeOUdiot98ru/r11f1YP1E3v/MFseD31shtMa3eQwqzz2tMFX/jBePUThHZeNUb97J0y6sa1pw+D3Wj66Ka3R72xjcbPxg/61rRmY/ZKe7VxXWOx229Y46B7nGhjPK8wDjafTDVaHVo96K3rceoo7krdYpGN2ztaqTe7Hv/pQ2s3p1+Oxno64dDjKdKjI0EgAYQ80A7a0iOsPOji4Hq1N3ay0Urx+h39EqTHZgr7G6u6//0fdfVo/fjyG6j56muojj79zvS6DUd2g4myG/Wc971J+/7jq+9unv8TztiYPf3e0WaV6Ouplp86temt7tWI80OkAcLRf3S8fhW0hr+6MekNPxc9OTX68fRLTwc0cDPoGV6mWtuQpnGoQ28MDEG/SnPS37qN66EVaMLdetMfsbIfIW1j/cH5aLsgbxH47tGTq7V64257U+S4vsDH8lCv01/U6Vc+mLDxYDYdfOjjLviQH2ynIcbpL2qHX0iv8KV3ZjNmJevBHtlK7IYH6ch8xrHDUvz7CvZuDMUAH6Kzt1Fl27HbWNfBK8I65Fvf1qYY2yfXrX2ShMBOSbVRXX5sB9cb/8/XF/gefXcDwSl4YId+p7vs1EbZPRxv0PkIrdGNfBAWT1/dmd5AuOLLrUUk8Z56f9DdnRYPRsc+++zmTu9JKGi5NdvTf5NmxOMWAY9gCo+Ty5PdYekQ4PwMlAYP/A/38mal3ry8wSZP4Ti99X+UUuwsnZsegkKgFw4DoyD4MHopSHlId44VQoAABXHspd9702q2D4munQ+BMDjxOMQ+Fn2m5VfqT6Mme99pMjXk0FJMeN3pI1lwhQ0b6KIhA2L7rel71hrt1+xtR+chP1vW1m/BZabq6jfG9lJINw4EVVNvq44HR7/PhWvPpQcOeFoE0DrsdgF/rXBkvm1Y6fSBIZMCo6f1EecENxRXBADBDxxt0LmMPI1jFNra3ZgV0ER8ovg8unZcAPAFtHi4Vs9imCj2Fojk2ViMu3w4XnYBRAS2HSH8LwqbdEgw/cR98EdazPbkEAR6rQkRlLIoq5HAsc49GcBY7qDETW8ajinrg444a3n6fk4vRnXBD49BpXAL3lb3WP6OdyOlhu/78GU9AjyCKGm31yAGlnIwoWTUDGWJlrQCHzuDPyrCToi5PYH2DH+SwnY4dDfYHmECq7V4OGiAcrQE5dobZqmCvt1S0Ldxh5zR2nSYH6BpbEI5BL6NSBTaqxVQzBn1xs4PP7J709iY1U3UwUZ3pHG3tdEktc1qX3u0Vj9LA8MnAA9YuXLSe7KVFCy6CmXOd5t29MCGKBizmZKTwIc2OG826+k/525Oio22BhU0FFXBJpxxM9CvIvivMgDSJ7sCm5FXuIPpC3YWF18kO2zOt3o/MfLEorcIHttSDCWLLzIvoBf5Wz6+ODRQDaiFrc+GGatuOe0KfW57PTZjG8AZvMcmBRecrddP9M/dDpb+xzF2umVjifTj/enXxgLXYEY8qWlpqJGMYXmh8t2tbr3dZkTGF/eIrsOYdIVcYkha2toCLQaWvDw/kOyUTO6JAUoDxUTYAh8A1XoNa/XjAAc5k2NMd7QsU02SRBJhVyFz+/PmLgF8Tqt6QZwg7EYf9Pu4J4y+N0Q5tUxcpI6VCn7eRV2n7BS5ymektBKXIXn9QnE19LbbWSbEjDQwRR+ixWcu4MYj54iGHAcO0ev3LvpvM6k/9C1H9kp63Sp6EO1x5qziaHWs4RFptgWQ+mIdqwbbAWZsqFvajA8L6JSQI2khPIjiILJMVgaEa8b3UEyIhYbEI7Q5F4vEiABrXDCGRwpLwS86UNZV2ryCpIxXqjCxeVJLBQmKCT9a5uBNtP7bsT99AB/LEO0Cqm4cZW4qe6DL96bT8pCmVECIKCYkjcv7Ps3cJQftORTqAvvtUHoMo10xbHqLHP9xlSM0PLTAGQx5Wch0txollICfUVX0nJ0mqIy8By5BGSzJ+wzFMtWxOS0c8agm38L+OwpfF08IodTWJlpHBxG1L1n4VnuqMMhjS1gDT3zILiJZLLjjl2tFRauivHY4/UohEsgSbD7zxRvSR5Kr8KFU4BRvKakqkaDi7V0VMNL3qZ5N+vDk6lyncxaiP6WmxjMytGuJzLOWOJxyGyOXBA3FKqg9KdULDZJweIoSpWdP6jK1KIpNZvqeym8oEp4SdbgqdS/zS4pIblUk8V6Tvlk2MxMtOgiJV5mRWC0SFJNdLhCom5Ic14u6IzYtiEaQSoHNiZ0X1fHxWru3g/GC0YsQneIqnyOcQiqXlZ8zRra7EJYJQSmgsuMTRzj0p48H1BpiDUpEwkUywZVgVIpqxCLl1lzbF9AFXGvKSaw8/GEkiID7QG9H00V5A6TzRpsR4G1zlexu+0KqKHBsjN7dGCtGLeKUAJ7CIbPDsBp7VbUS8bn2jrMk8WtkqtsRMJdKOA6zbD8CF3yszNYMmVpCWrNHTrtj2rlnXeT4DZqduGgdzF+t1Y/3UM9AqTlU70kzs2KEXTnFEtO3xNoQaXaGBj4URSLgc8AUrkFW6LhrmJGc9Kb3icUJ5lahX/Aticnym8tQWLULy9eD9AmgcoGsg3c/aKUQpATCFTjQABNNir89FVmV0BmDqhKxh7dYhvTb1sFdUt4wFK1dAPvYjONOiF901bqdmJjTJPceY2AHgsTJwOExm3swtDUlQORok6nTOSAovh9pZk9KTTASH+ZGX+/AjE9/J0zFQ5GfUc8PD0Un/0TH4HEiSAm3ph2Qme8+D/sEKjPvg6RKTlfYnbAvdFonHYXcH0ye3iGBq5TpUwFuveT83Dxj9B2huJRgCguQjaXzza9pq/C+bmDujxBRTQ2CKnjnPTvZpqPSc9anazQx4DvXh6UNU8lUE/hPNPZkMVr3MOadvN+MX2zlOayyGXvPLWtLTD4395wUk13k9Ct5lhO9GlkpwcIGaVO9vFlSBD1SpRhSTc4xwgRuN096BZUpUTWWaCOVzgcxHzhT48Bj37i9iNOID9nfc6ZN7JhbTrUAnu1sNtyzlOxmIaK+XqvvFtuns0j6IcLAJIboGdGAndlFz5jbPIUUgnumEOURgBAzdNFkUTtpdXgzCgdbKkAiCDaOi4roX+ad3VFTtF0hRDco7P2+UnUju9NkXtil86AAAdWmNQJij9u+y7lHdBal38/7hwWkQtUh+SrIEXUXh9jK5XIe2aPVgXO8HWEPaldQ+qTsGMqoe+geiQdTHNIsLweSDlERq69Ir3ok/y4FSGiGRT+SR5LMdKF1LIdv826zMp+X7Kae/c0aeSo3XgPBI/XERnFq91ed/OWqJlDuOAKbZJCltJFr0clWhaqLQZe7Hz8GbRdSzqT9YoDJCPhGozgs2MQ1qC9F6MR45YvLkx4Vfq7jTLAeZPN4sjALornQmTWXkHOPPQYXeXoG62p9nm+mWraeuBSV8VToMztIPLkmUlkZJscacou5pTZ1ROCzVY1sr+kabUWzMsy9QxSPb40dIjhXrhz9fAyzg89y7KvLtXpTNZkkJt3Twg5gBwelZnpTRbaZjrpkYyx0uGqMQakO1zPv9aQIPYSAQaLbQZPzfkA4b8zH0/lHYCClnlZ0gYDabrbu9rJPs/vbNNulMQ/gtx1K6NSzMzwvimpi1AHUExS+gFyMNEk5mfSH4N8EzmsEnVgsQ8Eh4o5lkJ2aNRN+EZnBiqb8h8HBu7cIu5iKKl5ckgbKA73543jAanUEGZmxminZqAGjdDC6uxF5AUKZcMUAbhw70BRvCJKB9VCGRaK7qW9RNoxIJqq4GFU7jIEcvk2oq32e5bs448yl+Co2ybeCZ9XXN5Dg+CtmgiRLgXG1Vi8hU0YBX1P3eWgU90QR323CNpqrlXpslnjDPDnXM1luj8ZRr1iBIz01N2Sj1ev26CbN6YpDrKZ0i7Hm9IEUJjM8/ejkpDHogBhIghtySaIgyfo/5xliPlC6UpIQcKpBUtTeDhwf9cgoDKvKgJRDcmtbor30DOCTcfsTBd7VQ1R4tg9gWZWhS8YrNer8ng+3lukCCw2744LkePD10GiknkspqQO17oEJ1vN3mc6YinBUffG0ZtUaP/2lStPMdRIwUxiCX/ohV8CLnYc4E8otNXEzxPW9mcy1Av0DpQuNMvOOW8o8afB5wu3qiqyz++JdodkQp0ah1RC26GDPO3s4fYTLmqw6zeOj1tiq/THoMtlJ3YKFIZJMU3Axuic0dVhRoUlh8nlzHcRMrkwNAmP0BCWZmXoKrtQsEUeJSTZU32z9Sa9oQc4wqrClcxahIN86qHZXaXhLH/5pDAavruoc3a4MHyaSp1oE8tP6dRc/1wI1rfkh1ccHk250cRkmvJPGcJPhUie5K10zyzhHcEL+M+v3EN+NYZf5boBBsKe0kSuUaPKwh/fAld6Opw/vXbg2InqYiY/IiUzUhiB8S7DxiLqbS6XIfZhfNyVToy6MHSDU6YNr6vY7kxtXt4wn84NwA4CbZja1LZPTZrTBgR74xV7zqu40ryb8MAiTLheYHZVoez335VxawukBB4pyW98Xdizb7nF9MIuol8R7yKysx9OH2IvM4MF5Lq6dNSVJyvnogwPEyw21yNjurS75JUyxqSWqQ49PdgY+45YMN7t0KMMItN6lS3fz6iEkZV2VrzCpTVP6OgPK6KwxIcVcuVvVLrnqF8Tel+6KLfQuY2d9qXMZG6j7wIrSeuYdsbZ6auLi0Enn+q6u6VaqruUuwg2/+Yg/U8Lcx5R11Lyn0GUHQ/jPbwGkko7uznJVJo4RnpUAI+/N5Ou7LFE/HlIvASA05HkJ58tEHLe6vdO1vjk6g/rGTO9z3bNouy+JndSHMPI+9iLJXLiqEOrM+sGVSgmef9mb9s6xg1a0l1TjjnN/SNq/SdHreDW+6lBL7MOUoo/uP9ETUHAcMjmvrpqvJBfP3dWVUO3EZegexqKnJ2HfGLqEzHhAatfQerxHhcCKfbiIzly7NrI8I6AF3+SLDRYsYkPMJ2RrN+39Tcz41ZruagPDTfZDBAWddcF63m2HP4NkCtI+6uB5W5A1WEr3G70St30UmBID6ub0sbE7KC5d7E+KjF1sMQuHALFWirNROgzHANfSNs86PnHXbnrXge5ym6a+TgDgoPIkcdjZxZf62oocS85bc/I+4CpELYxBPcQuDM07vzV9obyHZb0vWekxgJKv8fiY00USW7BTJM4SM8ItIB/IbXg6DU+9W2yr6YakT93ce7js0bo2k1OhgzNXplLrgm87letEZ+6F5QtFmWQsttCqHcptJD1SCVdYVnWdbBVa47kYpbbI/CJZnumKsjHeXwrVaXox4kCki98UkROMEtY5kLnDFO/z0EXB2OShYJiOB2K+bQr3DjGfMGDJV75eq6ep9xOGR8ImM2epXl+p+I6omRLCbFFd0URfLn+GrOV+2krc6gnXB6pb3gvZfHbf+Ie6J1AaW7SYOepwJ0beBK4Oku/OujRuizNHefF7Xo0zgmBDDrljeA2ISZnmMgfWEewmXczi6eKiWb5Z1+8BpXnnzCJv6gH0/FZ8XmRVdbbEBSvzDgrnW3388dmaf0Jwu42Go9/l6cq3UQ3pys6Pk8vXUmXi5as/vPj++Ys36s3rVz8/e40fXvxB/fTi2fevX73+7uVT9eqn50+vb/u86LPvb34vHv7im/XjR4/Wl4+/uri8vLx6cPVl9eWgs4CeWQ8XdBaxRtTsv250iwS+WcMB1re9XOdFmnmKp35EKnulxx5lGnzxHZWfDx9/qX5CHCBWe107o5YXBf66VGXqXGWWCX1dWcrLdjq/ChZsOakzp7wJuEMo2YsSNL4gQB9K8kd8Mu249OLBZeRx6VUmLuHsHCienr0omAaI+f2oOhHqdHOOiZF84atcpp73+XlH6o0SCqlujBSTWt9dGMzJBgR1KcsLWdNEFBk0ZQF6u4Vne1mW+XtcFIOi8Z4ejj2fRMIIsLyed//OXxifM5Tkjk/d/+tra598C3Hhbcbri8Yew4//B6lVWUeROQAA');

    function render_terms_content(): void
    {
        $html = gzdecode((string)base64_decode(EMBEDDED_TERMS_CONTENT_GZIP_BASE64, true));
        echo $html !== false ? $html : '<p>Termos temporariamente indisponiveis.</p>';
    }

    function render_terms_modal(array $user): void
    {
        $required = !user_has_accepted_current_terms($user);
        $acceptedAt = trim((string)($user['terms_accepted_at'] ?? ''));
        ?>
        <section class="terms-modal-backdrop<?= $required ? ' is-required' : ' is-hidden' ?>" data-terms-modal data-terms-required="<?= $required ? '1' : '0' ?>" role="dialog" aria-modal="true" aria-labelledby="terms-modal-title">
            <article class="terms-modal">
                <header class="terms-modal-header"><div><span>LigFlow</span><h2 id="terms-modal-title">Termos de Uso e Política de Privacidade</h2><p><?= $required ? 'Leia o documento e confirme o aceite para continuar utilizando a plataforma.' : 'Consulte novamente os termos vigentes para uso do LigFlow.' ?></p></div><?php if (!$required): ?><button class="icon-button" type="button" data-close-terms aria-label="Fechar termos">x</button><?php endif; ?></header>
                <div class="terms-modal-content" tabindex="0"><?php render_terms_content(); ?></div>
                <footer class="terms-modal-footer">
                    <?php if ($required): ?><form method="post" class="terms-acceptance-form"><input type="hidden" name="action" value="accept_terms"><input type="hidden" name="terms_version" value="<?= h(TERMS_VERSION) ?>"><label class="check"><input type="checkbox" name="terms_acceptance" value="1" required data-terms-checkbox> Li e aceito os Termos de Uso e a Política de Privacidade do LigFlow.</label><button class="button" type="submit" disabled data-accept-terms>Continuar</button></form>
                    <?php else: ?><small>Versão <?= h(TERMS_VERSION) ?><?= $acceptedAt !== '' ? ' - aceita em ' . h(datetime_utc_display($acceptedAt)) : '' ?></small><button class="button secondary" type="button" data-close-terms>Fechar</button><?php endif; ?>
                </footer>
            </article>
        </section>
        <?php
    }
}

function load_env_file(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    initialize_database($pdo);
    $pdo->sqliteCreateFunction(
        'ligflow_local_datetime',
        static fn ($value): string => datetime_utc_display((string)$value, 'Y-m-d H:i:s'),
        1
    );
    return $pdo;
}

function initialize_database(PDO $pdo): void
{
    $currentVersion = (int)$pdo->query('PRAGMA user_version')->fetchColumn();
    if ($currentVersion >= DB_SCHEMA_VERSION) {
        return;
    }

    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $currentVersion = (int)$pdo->query('PRAGMA user_version')->fetchColumn();
        if ($currentVersion < DB_SCHEMA_VERSION) {
            migrate($pdo);
            seed($pdo);
            ensure_default_access_profiles($pdo);
            if ($currentVersion < 21) {
                migrate_legacy_local_datetimes_to_utc($pdo);
            }
            $pdo->exec('PRAGMA user_version = ' . DB_SCHEMA_VERSION);
        }
        $pdo->exec('COMMIT');
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
        throw $error;
    }
}

function migrate(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS companies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            legal_name TEXT NOT NULL,
            trade_name TEXT NOT NULL,
            cnpj TEXT,
            email TEXT,
            phone TEXT,
            plan TEXT DEFAULT 'MVP',
            max_users INTEGER DEFAULT 10,
            max_agents INTEGER DEFAULT 5,
            max_channels INTEGER DEFAULT 2,
            monthly_minutes_limit INTEGER DEFAULT 1000,
            status TEXT DEFAULT 'Ativa',
            timezone TEXT DEFAULT 'America/Sao_Paulo',
            call_window TEXT DEFAULT '08:00-18:00',
            voip_provider TEXT DEFAULT 'Nvoip',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER,
            team_id INTEGER,
            access_profile_id INTEGER,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL,
            allowed_modules_json TEXT,
            phone TEXT,
            extension TEXT,
            status TEXT DEFAULT 'Disponivel',
            work_hours TEXT DEFAULT '08:00-18:00',
            two_factor_enabled INTEGER DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS password_reset_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token_hash TEXT NOT NULL UNIQUE,
            pending_password_hash TEXT NOT NULL,
            expires_at TEXT NOT NULL,
            used_at TEXT,
            requested_ip TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS access_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER,
            name TEXT NOT NULL,
            role_key TEXT DEFAULT 'usuario_operacional',
            modules_json TEXT NOT NULL DEFAULT '[]',
            created_by INTEGER,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS teams (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            description TEXT,
            supervisor_id INTEGER,
            daily_goal INTEGER DEFAULT 100,
            simultaneous_limit INTEGER DEFAULT 1,
            priority INTEGER DEFAULT 1,
            voip_queue TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS contact_lists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            description TEXT,
            source TEXT DEFAULT 'CSV',
            status TEXT DEFAULT 'Disponivel',
            tags TEXT,
            created_by INTEGER,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            list_id INTEGER NOT NULL,
            name TEXT,
            phone_raw TEXT,
            phone_e164 TEXT NOT NULL,
            email TEXT,
            organization TEXT,
            city TEXT,
            state TEXT,
            product TEXT,
            origin TEXT,
            document TEXT,
            external_code TEXT,
            notes TEXT,
            custom_json TEXT DEFAULT '{}',
            status TEXT DEFAULT 'novo',
            attempts INTEGER DEFAULT 0,
            last_call_at TEXT,
            reserved_by INTEGER,
            reserved_at TEXT,
            reservation_expires_at TEXT,
            UNIQUE(company_id, list_id, phone_e164)
        );

        CREATE TABLE IF NOT EXISTS import_batches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            list_id INTEGER NOT NULL,
            filename TEXT,
            total_rows INTEGER DEFAULT 0,
            imported INTEGER DEFAULT 0,
            duplicated INTEGER DEFAULT 0,
            invalid INTEGER DEFAULT 0,
            blocked INTEGER DEFAULT 0,
            error_rows TEXT DEFAULT '[]',
            created_by INTEGER,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS campaigns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            list_id INTEGER,
            team_id INTEGER,
            supervisor_id INTEGER,
            name TEXT NOT NULL,
            description TEXT,
            dialer_type TEXT DEFAULT 'progressivo',
            caller_id TEXT,
            sip_trunk TEXT DEFAULT 'Tronco simulado',
            script TEXT,
            starts_at TEXT,
            ends_at TEXT,
            call_window TEXT DEFAULT '08:00-18:00',
            max_attempts INTEGER DEFAULT 1,
            simultaneous_calls INTEGER NOT NULL DEFAULT 1,
            retry_interval_minutes INTEGER DEFAULT 240,
            priority INTEGER DEFAULT 1,
            recording_enabled INTEGER DEFAULT 0,
            status TEXT DEFAULT 'Ativa',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS call_results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER,
            name TEXT NOT NULL,
            action TEXT DEFAULT 'concluir',
            is_default INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS calls (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            campaign_id INTEGER NOT NULL,
            contact_id INTEGER NOT NULL,
            agent_id INTEGER,
            provider TEXT DEFAULT 'Nvoip',
            external_call_id TEXT,
            provider_call_id TEXT,
            origin_number TEXT,
            destination_number TEXT,
            status TEXT DEFAULT 'queued',
            provider_status_raw TEXT,
            internal_status TEXT,
            attempt_number INTEGER DEFAULT 1,
            error_message TEXT,
            started_at TEXT,
            ringing_at TEXT,
            answered_at TEXT,
            ended_at TEXT,
            duration_seconds INTEGER DEFAULT 0,
            billable_seconds INTEGER DEFAULT 0,
            result_id INTEGER,
            notes TEXT,
            recording_url TEXT,
            estimated_cost REAL DEFAULT 0,
            confirmed_cost REAL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS dial_batches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            campaign_id INTEGER NOT NULL,
            agent_id INTEGER NOT NULL,
            requested_parallelism INTEGER NOT NULL DEFAULT 1,
            effective_parallelism INTEGER NOT NULL DEFAULT 1,
            telephony_mode TEXT NOT NULL,
            telephony_trunk TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'ORIGINATING',
            winner_call_id INTEGER,
            idempotency_key TEXT NOT NULL UNIQUE,
            next_started_at TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS call_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            call_id INTEGER,
            event_name TEXT NOT NULL,
            old_status TEXT,
            new_status TEXT,
            payload TEXT DEFAULT '{}',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS nvoip_webhook_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER,
            call_id INTEGER,
            status TEXT,
            recording_url TEXT,
            match_key TEXT,
            payload TEXT DEFAULT '{}',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS call_status_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER,
            call_id INTEGER,
            provider TEXT,
            status TEXT,
            message TEXT,
            payload TEXT DEFAULT '{}',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS callbacks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            campaign_id INTEGER,
            contact_id INTEGER NOT NULL,
            agent_id INTEGER,
            scheduled_at TEXT NOT NULL,
            priority TEXT DEFAULT 'normal',
            reason TEXT,
            notes TEXT,
            status TEXT DEFAULT 'pendente',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS blocklist (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            phone_e164 TEXT NOT NULL,
            reason TEXT NOT NULL,
            source TEXT DEFAULT 'manual',
            responsible_user_id INTEGER,
            notes TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(company_id, phone_e164)
        );

        CREATE TABLE IF NOT EXISTS agent_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            agent_id INTEGER NOT NULL,
            status TEXT DEFAULT 'disponivel',
            current_campaign_id INTEGER,
            connected_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER,
            user_id INTEGER,
            action TEXT NOT NULL,
            resource TEXT,
            ip_address TEXT,
            old_data TEXT,
            new_data TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS integration_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            provider TEXT NOT NULL DEFAULT 'nvoip',
            integration_name TEXT,
            mode TEXT NOT NULL DEFAULT 'simulate',
            auth_method TEXT DEFAULT 'napikey',
            api_url TEXT,
            napikey TEXT,
            numbersip TEXT,
            user_sip TEXT,
            sip_wss_url TEXT DEFAULT 'wss://app.nvoip.com.br:7443',
            sip_domain TEXT DEFAULT 'app.nvoip.com.br',
            sip_password TEXT,
            auto_answer_nvoip_callback INTEGER DEFAULT 0,
            sip_callback_timeout_seconds INTEGER DEFAULT 60,
            user_token TEXT,
            payload_template TEXT,
            origin_number TEXT,
            price_per_minute REAL DEFAULT 0.06,
            recording_enabled INTEGER DEFAULT 1,
            webhook_url TEXT,
            webhook_secret TEXT,
            extra_config TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(company_id, provider)
        );

        CREATE TABLE IF NOT EXISTS consultant_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            user_id INTEGER,
            team_id INTEGER,
            display_name TEXT NOT NULL,
            internal_code TEXT,
            status TEXT DEFAULT 'Ativo',
            goal INTEGER DEFAULT 0,
            operational_config TEXT DEFAULT '{}',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            included_minutes INTEGER DEFAULT 0,
            max_users INTEGER DEFAULT 1,
            max_consultants INTEGER DEFAULT 1,
            max_lists INTEGER DEFAULT 10,
            max_contacts INTEGER DEFAULT 1000,
            commercial_price_per_minute REAL DEFAULT 0,
            status TEXT DEFAULT 'Ativo',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL UNIQUE,
            plan_id INTEGER,
            plan_name TEXT,
            starts_at TEXT,
            renews_at TEXT,
            included_minutes INTEGER DEFAULT 0,
            max_users INTEGER DEFAULT 1,
            max_consultants INTEGER DEFAULT 1,
            max_lists INTEGER DEFAULT 10,
            max_contacts INTEGER DEFAULT 1000,
            commercial_price_per_minute REAL DEFAULT 0,
            status TEXT DEFAULT 'Ativa',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS payment_settings (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            active INTEGER DEFAULT 0,
            environment TEXT DEFAULT 'test',
            public_key TEXT,
            access_token_encrypted TEXT,
            webhook_secret_encrypted TEXT,
            pix_enabled INTEGER DEFAULT 1,
            card_enabled INTEGER DEFAULT 1,
            boleto_enabled INTEGER DEFAULT 1,
            updated_by INTEGER,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS google_places_settings (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            active INTEGER NOT NULL DEFAULT 0,
            api_key_encrypted TEXT,
            updated_by INTEGER,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS radar_lead_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            list_id INTEGER,
            place_id TEXT NOT NULL,
            phone_e164 TEXT,
            search_json TEXT NOT NULL DEFAULT '{}',
            created_by INTEGER,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(company_id, place_id),
            UNIQUE(company_id, phone_e164)
        );

        CREATE TABLE IF NOT EXISTS asterisk_settings (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            enabled INTEGER NOT NULL DEFAULT 0,
            environment TEXT NOT NULL DEFAULT 'test',
            active_mode TEXT NOT NULL DEFAULT 'NVOIP_DIRECT',
            active_route TEXT NOT NULL DEFAULT 'NVOIP_TRUNK',
            ari_url TEXT,
            ari_ws_url TEXT,
            ari_username TEXT,
            ari_password_encrypted TEXT,
            stasis_app TEXT NOT NULL DEFAULT 'ligflow',
            originate_timeout_seconds INTEGER NOT NULL DEFAULT 30,
            bridge_timeout_seconds INTEGER NOT NULL DEFAULT 15,
            reconnect_initial_seconds INTEGER NOT NULL DEFAULT 2,
            reconnect_max_seconds INTEGER NOT NULL DEFAULT 30,
            sip_wss_url TEXT,
            sip_domain TEXT,
            consultant_endpoint TEXT,
            nvoip_trunk TEXT NOT NULL DEFAULT 'nvoip',
            directcall_trunk TEXT NOT NULL DEFAULT 'directcall',
            webrtc_password_encrypted TEXT,
            webrtc_context TEXT,
            nvoip_trunk_config_json TEXT DEFAULT '{}',
            directcall_trunk_config_json TEXT DEFAULT '{}',
            extension_start INTEGER NOT NULL DEFAULT 1000,
            extension_end INTEGER NOT NULL DEFAULT 9999,
            provisioning_agent_url TEXT,
            provisioning_agent_secret_encrypted TEXT,
            provisioning_agent_timeout_seconds INTEGER NOT NULL DEFAULT 10,
            updated_by INTEGER,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS asterisk_user_extensions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            asterisk_server_id INTEGER NOT NULL DEFAULT 1,
            extension TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'Ativo',
            provisioning_status TEXT NOT NULL DEFAULT 'Pendente',
            lifecycle_status TEXT NOT NULL DEFAULT 'ACTIVE',
            provisioned_at TEXT,
            last_provision_error TEXT,
            provisioning_version INTEGER NOT NULL DEFAULT 1,
            released_at TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            deactivated_at TEXT
        );
        CREATE TABLE IF NOT EXISTS asterisk_provisioning_jobs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            asterisk_user_extension_id INTEGER NOT NULL,
            asterisk_server_id INTEGER NOT NULL DEFAULT 1,
            operation TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'PENDING',
            idempotency_key TEXT NOT NULL UNIQUE,
            attempts INTEGER NOT NULL DEFAULT 0,
            last_error TEXT,
            payload_json TEXT NOT NULL DEFAULT '{}',
            processing_started_at TEXT,
            completed_at TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS asterisk_ari_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_key TEXT NOT NULL UNIQUE,
            call_id INTEGER,
            event_type TEXT NOT NULL,
            payload_json TEXT NOT NULL DEFAULT '{}',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            plan_id INTEGER,
            amount REAL NOT NULL,
            currency TEXT DEFAULT 'BRL',
            billing_period TEXT NOT NULL,
            limits_snapshot_json TEXT NOT NULL,
            internal_reference TEXT NOT NULL UNIQUE,
            idempotency_key TEXT NOT NULL UNIQUE,
            provider TEXT DEFAULT 'mercado_pago',
            provider_payment_id TEXT,
            payment_method TEXT,
            status TEXT NOT NULL DEFAULT 'CREATED',
            provider_status TEXT,
            provider_status_detail TEXT,
            checkout_data_json TEXT DEFAULT '{}',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            approved_at TEXT,
            expires_at TEXT,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS subscription_periods (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            subscription_id INTEGER NOT NULL,
            plan_id INTEGER,
            payment_id INTEGER NOT NULL UNIQUE,
            starts_at TEXT NOT NULL,
            ends_at TEXT NOT NULL,
            limits_snapshot_json TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS telephony_ledger (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            subscription_id INTEGER NOT NULL,
            subscription_period_id INTEGER,
            call_id INTEGER,
            entry_type TEXT NOT NULL,
            amount_micros INTEGER NOT NULL,
            balance_before_micros INTEGER NOT NULL,
            balance_after_micros INTEGER NOT NULL,
            idempotency_key TEXT NOT NULL UNIQUE,
            reference_type TEXT,
            reference_id INTEGER,
            notes TEXT,
            responsible_user_id INTEGER,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS payment_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            payment_id INTEGER NOT NULL,
            event_name TEXT NOT NULL,
            payload_json TEXT DEFAULT '{}',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS integration_client_links (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            integration_id INTEGER,
            origin_rule TEXT,
            max_simultaneous_calls INTEGER DEFAULT 1,
            monthly_minutes_limit INTEGER DEFAULT 0,
            internal_cost_per_minute REAL DEFAULT 0,
            commercial_price_per_minute REAL DEFAULT 0,
            recording_enabled INTEGER DEFAULT 1,
            calls_enabled INTEGER DEFAULT 1,
            calls_blocked INTEGER DEFAULT 0,
            status TEXT DEFAULT 'Ativo',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(company_id, integration_id)
        );
    ");
    ensure_column($pdo, 'integration_settings', 'integration_name', 'TEXT');
    ensure_column($pdo, 'integration_settings', 'auth_method', "TEXT DEFAULT 'napikey'");
    ensure_column($pdo, 'integration_settings', 'numbersip', 'TEXT');
    ensure_column($pdo, 'integration_settings', 'user_sip', 'TEXT');
    ensure_column($pdo, 'integration_settings', 'sip_wss_url', "TEXT DEFAULT 'wss://app.nvoip.com.br:7443'");
    ensure_column($pdo, 'integration_settings', 'sip_domain', "TEXT DEFAULT 'app.nvoip.com.br'");
    ensure_column($pdo, 'integration_settings', 'sip_password', 'TEXT');
    ensure_column($pdo, 'integration_settings', 'auto_answer_nvoip_callback', 'INTEGER DEFAULT 0');
    ensure_column($pdo, 'integration_settings', 'sip_callback_timeout_seconds', 'INTEGER DEFAULT 60');
    ensure_column($pdo, 'integration_settings', 'payload_template', 'TEXT');
    ensure_column($pdo, 'integration_settings', 'extra_config', 'TEXT');
    ensure_column($pdo, 'users', 'avatar_path', 'TEXT');
    ensure_column($pdo, 'users', 'created_by', 'INTEGER');
    ensure_column($pdo, 'users', 'access_profile_id', 'INTEGER');
    ensure_column($pdo, 'users', 'allowed_modules_json', 'TEXT');
    ensure_column($pdo, 'users', 'deleted_at', 'TEXT');
    ensure_column($pdo, 'users', 'terms_accepted_version', 'TEXT');
    ensure_column($pdo, 'users', 'terms_accepted_at', 'TEXT');
    ensure_column($pdo, 'calls', 'provider_call_id', 'TEXT');
    ensure_column($pdo, 'calls', 'provider_status_raw', 'TEXT');
    ensure_column($pdo, 'calls', 'internal_status', 'TEXT');
    ensure_column($pdo, 'calls', 'attempt_number', 'INTEGER DEFAULT 1');
    ensure_column($pdo, 'calls', 'error_message', 'TEXT');
    ensure_column($pdo, 'calls', 'billing_rate_micros', 'INTEGER');
    ensure_column($pdo, 'calls', 'estimated_cost_micros', 'INTEGER');
    ensure_column($pdo, 'calls', 'telephony_period_id', 'INTEGER');
    ensure_column($pdo, 'calls', 'telephony_mode', "TEXT DEFAULT 'NVOIP_DIRECT'");
    ensure_column($pdo, 'calls', 'telephony_trunk', 'TEXT');
    ensure_column($pdo, 'calls', 'provider_channel_id', 'TEXT');
    ensure_column($pdo, 'calls', 'provider_linked_id', 'TEXT');
    ensure_column($pdo, 'calls', 'provider_bridge_id', 'TEXT');
    ensure_column($pdo, 'calls', 'event_origin', 'TEXT');
    ensure_column($pdo, 'calls', 'last_event_at', 'TEXT');
    ensure_column($pdo, 'calls', 'connected_at', 'TEXT');
    ensure_column($pdo, 'calls', 'finalized_at', 'TEXT');
    ensure_column($pdo, 'calls', 'hangup_cause', 'TEXT');
    ensure_column($pdo, 'asterisk_settings', 'webrtc_password_encrypted', 'TEXT');
    ensure_column($pdo, 'asterisk_settings', 'webrtc_context', 'TEXT');
    ensure_column($pdo, 'asterisk_settings', 'extension_start', 'INTEGER NOT NULL DEFAULT 1000');
    ensure_column($pdo, 'asterisk_settings', 'extension_end', 'INTEGER NOT NULL DEFAULT 9999');
    ensure_column($pdo, 'asterisk_settings', 'provisioning_agent_url', 'TEXT');
    ensure_column($pdo, 'asterisk_settings', 'provisioning_agent_secret_encrypted', 'TEXT');
    ensure_column($pdo, 'asterisk_settings', 'provisioning_agent_timeout_seconds', 'INTEGER NOT NULL DEFAULT 10');
    $pdo->exec("CREATE TABLE IF NOT EXISTS asterisk_user_extensions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        asterisk_server_id INTEGER NOT NULL DEFAULT 1,
        extension TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'Ativo',
        provisioning_status TEXT NOT NULL DEFAULT 'Pendente',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
        deactivated_at TEXT
    )");
    ensure_column($pdo, 'asterisk_user_extensions', 'lifecycle_status', "TEXT NOT NULL DEFAULT 'ACTIVE'");
    ensure_column($pdo, 'asterisk_user_extensions', 'provisioned_at', 'TEXT');
    ensure_column($pdo, 'asterisk_user_extensions', 'last_provision_error', 'TEXT');
    ensure_column($pdo, 'asterisk_user_extensions', 'provisioning_version', 'INTEGER NOT NULL DEFAULT 1');
    ensure_column($pdo, 'asterisk_user_extensions', 'released_at', 'TEXT');
    ensure_column($pdo, 'asterisk_user_extensions', 'sip_password_encrypted', 'TEXT');
    ensure_column($pdo, 'asterisk_provisioning_jobs', 'response_json', 'TEXT');
    $pdo->exec("CREATE TABLE IF NOT EXISTS asterisk_provisioning_jobs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        asterisk_user_extension_id INTEGER NOT NULL,
        asterisk_server_id INTEGER NOT NULL DEFAULT 1,
        operation TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'PENDING',
        idempotency_key TEXT NOT NULL UNIQUE,
        attempts INTEGER NOT NULL DEFAULT 0,
        last_error TEXT,
        payload_json TEXT NOT NULL DEFAULT '{}',
        processing_started_at TEXT,
        completed_at TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_asterisk_user_extensions_active_extension
        ON asterisk_user_extensions(company_id, asterisk_server_id, extension)
        WHERE status = 'Ativo'");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_asterisk_user_extensions_active_user
        ON asterisk_user_extensions(company_id, user_id, asterisk_server_id)
        WHERE status = 'Ativo'");
    ensure_index($pdo, 'idx_asterisk_user_extensions_lifecycle', 'CREATE INDEX IF NOT EXISTS idx_asterisk_user_extensions_lifecycle ON asterisk_user_extensions(company_id, asterisk_server_id, lifecycle_status, extension)');
    ensure_index($pdo, 'idx_asterisk_provisioning_jobs_extension_status', 'CREATE INDEX IF NOT EXISTS idx_asterisk_provisioning_jobs_extension_status ON asterisk_provisioning_jobs(asterisk_user_extension_id, status, id DESC)');
    ensure_index($pdo, 'idx_asterisk_provisioning_jobs_company_user', 'CREATE INDEX IF NOT EXISTS idx_asterisk_provisioning_jobs_company_user ON asterisk_provisioning_jobs(company_id, user_id, id DESC)');
    ensure_column($pdo, 'callbacks', 'call_id', 'INTEGER');
    ensure_column($pdo, 'callbacks', 'completed_at', 'TEXT');
    ensure_column($pdo, 'plans', 'monthly_price', 'REAL DEFAULT 0');
    ensure_column($pdo, 'plans', 'setup_fee', 'REAL DEFAULT 0');
    ensure_column($pdo, 'plans', 'billing_period', "TEXT DEFAULT 'Mensal'");
    ensure_column($pdo, 'plans', 'payment_type', "TEXT DEFAULT 'Pix'");
    ensure_column($pdo, 'plans', 'description', 'TEXT');
    ensure_column($pdo, 'plans', 'telephony_credit_micros', 'INTEGER');
    ensure_column($pdo, 'plans', 'telephony_rate_micros', 'INTEGER');
    ensure_column($pdo, 'subscriptions', 'telephony_period_id', 'INTEGER');
    ensure_column($pdo, 'subscriptions', 'telephony_credit_initial_micros', 'INTEGER');
    ensure_column($pdo, 'subscriptions', 'telephony_rate_micros', 'INTEGER');
    ensure_column($pdo, 'subscriptions', 'telephony_balance_micros', 'INTEGER');
    ensure_column($pdo, 'subscription_periods', 'telephony_credit_initial_micros', 'INTEGER');
    ensure_column($pdo, 'subscription_periods', 'telephony_rate_micros', 'INTEGER');
    ensure_column($pdo, 'subscription_periods', 'telephony_balance_micros', 'INTEGER');
    ensure_column($pdo, 'contact_lists', 'radar_target_leads', 'INTEGER');
    ensure_column($pdo, 'campaigns', 'simultaneous_calls', 'INTEGER NOT NULL DEFAULT 1');
    ensure_column($pdo, 'calls', 'dial_batch_id', 'INTEGER');
    ensure_column($pdo, 'calls', 'race_outcome', 'TEXT');
    $dialBatchColumns = array_column($pdo->query('PRAGMA table_info(dial_batches)')->fetchAll(), 'name');
    ensure_column($pdo, 'dial_batches', 'requested_parallelism', 'INTEGER NOT NULL DEFAULT 1');
    ensure_column($pdo, 'dial_batches', 'effective_parallelism', 'INTEGER NOT NULL DEFAULT 1');
    ensure_column($pdo, 'dial_batches', 'telephony_trunk', "TEXT NOT NULL DEFAULT ''");
    ensure_column($pdo, 'dial_batches', 'next_started_at', 'TEXT');
    if (in_array('requested_calls', $dialBatchColumns, true)) {
        $pdo->exec('UPDATE dial_batches SET requested_parallelism = requested_calls WHERE requested_calls > 0');
    }
    if (in_array('effective_calls', $dialBatchColumns, true)) {
        $pdo->exec('UPDATE dial_batches SET effective_parallelism = effective_calls WHERE effective_calls > 0');
    }
    if (in_array('trunk_route', $dialBatchColumns, true)) {
        $pdo->exec("UPDATE dial_batches SET telephony_trunk = trunk_route WHERE trim(COALESCE(trunk_route, '')) <> ''");
    }
    if (in_array('continuation_started_at', $dialBatchColumns, true)) {
        $pdo->exec('UPDATE dial_batches SET next_started_at = continuation_started_at WHERE next_started_at IS NULL AND continuation_started_at IS NOT NULL');
    }
    $pdo->exec("UPDATE callbacks SET status = CASE
        WHEN status IS NULL OR trim(status) = '' OR lower(trim(status)) = 'pending' THEN 'pendente'
        ELSE lower(trim(status)) END");
    $pdo->exec("UPDATE callbacks SET scheduled_at = CASE
        WHEN length(replace(scheduled_at, 'T', ' ')) = 16 THEN replace(scheduled_at, 'T', ' ') || ':00'
        ELSE replace(scheduled_at, 'T', ' ') END");
    ensure_index($pdo, 'idx_calls_company_campaign_created', 'CREATE INDEX IF NOT EXISTS idx_calls_company_campaign_created ON calls(company_id, campaign_id, created_at DESC)');
    ensure_index($pdo, 'idx_dial_batches_active_agent', 'CREATE INDEX IF NOT EXISTS idx_dial_batches_active_agent ON dial_batches(company_id, agent_id, status, id DESC)');
    ensure_index($pdo, 'idx_dial_batches_campaign_created', 'CREATE INDEX IF NOT EXISTS idx_dial_batches_campaign_created ON dial_batches(company_id, campaign_id, created_at DESC)');
    ensure_index($pdo, 'idx_calls_dial_batch', 'CREATE INDEX IF NOT EXISTS idx_calls_dial_batch ON calls(dial_batch_id, status, id)');
    ensure_index($pdo, 'idx_radar_lead_history_company_list', 'CREATE INDEX IF NOT EXISTS idx_radar_lead_history_company_list ON radar_lead_history(company_id, list_id, created_at DESC)');
    ensure_index($pdo, 'idx_calls_company_campaign_internal_status', 'CREATE INDEX IF NOT EXISTS idx_calls_company_campaign_internal_status ON calls(company_id, campaign_id, internal_status)');
    ensure_index($pdo, 'idx_calls_company_destination_number', 'CREATE INDEX IF NOT EXISTS idx_calls_company_destination_number ON calls(company_id, destination_number)');
    ensure_index($pdo, 'idx_calls_company_provider_call_id', 'CREATE INDEX IF NOT EXISTS idx_calls_company_provider_call_id ON calls(company_id, provider_call_id)');
    ensure_index($pdo, 'idx_calls_provider_channel_id', 'CREATE INDEX IF NOT EXISTS idx_calls_provider_channel_id ON calls(provider_channel_id)');
    ensure_index($pdo, 'idx_calls_company_external_id', 'CREATE INDEX IF NOT EXISTS idx_calls_company_external_id ON calls(company_id, external_call_id)');
    ensure_index($pdo, 'idx_calls_company_agent_id', 'CREATE INDEX IF NOT EXISTS idx_calls_company_agent_id ON calls(company_id, agent_id, id DESC)');
    ensure_index($pdo, 'idx_callbacks_company_call', 'CREATE INDEX IF NOT EXISTS idx_callbacks_company_call ON callbacks(company_id, call_id)');
    ensure_index($pdo, 'idx_callbacks_company_agent_status_scheduled', 'CREATE INDEX IF NOT EXISTS idx_callbacks_company_agent_status_scheduled ON callbacks(company_id, agent_id, status, scheduled_at)');
    ensure_index($pdo, 'idx_payments_company_created', 'CREATE INDEX IF NOT EXISTS idx_payments_company_created ON payments(company_id, created_at DESC)');
    ensure_index($pdo, 'idx_payments_company_status', 'CREATE INDEX IF NOT EXISTS idx_payments_company_status ON payments(company_id, status)');
    ensure_index($pdo, 'idx_payments_provider_id', 'CREATE UNIQUE INDEX IF NOT EXISTS idx_payments_provider_id ON payments(provider, provider_payment_id) WHERE provider_payment_id IS NOT NULL');
    ensure_index($pdo, 'idx_subscription_periods_company_end', 'CREATE INDEX IF NOT EXISTS idx_subscription_periods_company_end ON subscription_periods(company_id, ends_at DESC)');
    ensure_index($pdo, 'idx_telephony_ledger_company_created', 'CREATE INDEX IF NOT EXISTS idx_telephony_ledger_company_created ON telephony_ledger(company_id, created_at DESC)');
    ensure_index($pdo, 'idx_telephony_ledger_period_created', 'CREATE INDEX IF NOT EXISTS idx_telephony_ledger_period_created ON telephony_ledger(subscription_period_id, created_at DESC)');
    ensure_index($pdo, 'idx_telephony_ledger_call', 'CREATE UNIQUE INDEX IF NOT EXISTS idx_telephony_ledger_call ON telephony_ledger(call_id) WHERE call_id IS NOT NULL');
    migrate_call_attempt_columns($pdo);
    migrate_contacts_unique_per_list($pdo);
}

function ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $columns = $pdo->query("PRAGMA table_info({$table})")->fetchAll();
    foreach ($columns as $existing) {
        if (($existing['name'] ?? '') === $column) {
            return;
        }
    }
    $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
}

function ensure_index(PDO $pdo, string $indexName, string $sql): void
{
    $exists = one("SELECT name FROM sqlite_master WHERE type = 'index' AND name = ?", [$indexName]);
    if ($exists) {
        return;
    }
    $pdo->exec($sql);
}

function migrate_call_attempt_columns(PDO $pdo): void
{
    $rows = $pdo->query("SELECT id, status, provider_status_raw, internal_status, external_call_id, provider_call_id, answered_at, ended_at, duration_seconds, error_message FROM calls")->fetchAll();
    if (!$rows) {
        return;
    }

    $terminalPayloads = [];
    $events = $pdo->query("SELECT call_id, payload FROM call_events WHERE event_name IN ('sip.ended', 'sip.failed') ORDER BY id ASC")->fetchAll();
    foreach ($events as $event) {
        $payload = json_decode((string)($event['payload'] ?? ''), true);
        if (is_array($payload)) {
            $terminalPayloads[(int)$event['call_id']] = $payload;
        }
    }

    $update = $pdo->prepare("UPDATE calls
        SET provider_call_id = COALESCE(NULLIF(provider_call_id, ''), NULLIF(external_call_id, '')),
            provider_status_raw = ?,
            internal_status = ?,
            attempt_number = COALESCE(NULLIF(attempt_number, 0), 1),
            error_message = ?
        WHERE id = ?");

    foreach ($rows as $row) {
        $rawStatus = trim((string)($row['provider_status_raw'] ?? ''));
        $errorMessage = trim((string)($row['error_message'] ?? ''));
        $terminalPayload = $terminalPayloads[(int)$row['id']] ?? null;
        if (is_array($terminalPayload) && in_array(strtolower($rawStatus), ['', 'in_progress', 'ringing', 'calling_origin'], true)) {
            $rawStatus = trim((string)($terminalPayload['cause'] ?? ''));
            if ($rawStatus === '') {
                $rawStatus = trim((string)($terminalPayload['sip_reason'] ?? ''));
            }
            if ($rawStatus === '') {
                $rawStatus = (string)($terminalPayload['event'] ?? $row['status'] ?? '');
            }
            $errorParts = [];
            $sipCode = (int)($terminalPayload['sip_code'] ?? 0);
            $sipReason = trim((string)($terminalPayload['sip_reason'] ?? ''));
            if ($sipCode > 0) {
                $errorParts[] = 'SIP ' . $sipCode;
            }
            if ($sipReason !== '' && strcasecmp($sipReason, $rawStatus) !== 0) {
                $errorParts[] = $sipReason;
            }
            if ($rawStatus !== '') {
                $errorParts[] = $rawStatus;
            }
            $errorMessage = implode(' - ', array_values(array_unique($errorParts)));
        }
        $normalized = normalize_call_attempt_status((string)($rawStatus ?: $row['status'] ?: ''), [
            'answered_at' => $row['answered_at'] ?? null,
            'ended_at' => $row['ended_at'] ?? null,
            'duration_seconds' => (int)($row['duration_seconds'] ?? 0),
            'event' => is_array($terminalPayload) ? (string)($terminalPayload['event'] ?? '') : '',
            'cause' => is_array($terminalPayload) ? (string)($terminalPayload['cause'] ?? '') : '',
            'reason' => is_array($terminalPayload) ? (string)($terminalPayload['sip_reason'] ?? '') : '',
            'stopped_by_user' => is_array($terminalPayload) && !empty($terminalPayload['stopped_by_user']),
        ]);
        $update->execute([
            $rawStatus !== '' ? $rawStatus : null,
            $normalized,
            $errorMessage !== '' ? $errorMessage : null,
            $row['id'],
        ]);
    }
}

function migrate_contacts_unique_per_list(PDO $pdo): void
{
    $schema = (string)$pdo->query("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'contacts'")->fetchColumn();
    if (!str_contains($schema, 'UNIQUE(company_id, phone_e164)')) {
        return;
    }

    $pdo->exec('PRAGMA foreign_keys = OFF');
    $pdo->exec("
        CREATE TABLE contacts_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            list_id INTEGER NOT NULL,
            name TEXT,
            phone_raw TEXT,
            phone_e164 TEXT NOT NULL,
            email TEXT,
            organization TEXT,
            city TEXT,
            state TEXT,
            product TEXT,
            origin TEXT,
            document TEXT,
            external_code TEXT,
            notes TEXT,
            custom_json TEXT DEFAULT '{}',
            status TEXT DEFAULT 'novo',
            attempts INTEGER DEFAULT 0,
            last_call_at TEXT,
            reserved_by INTEGER,
            reserved_at TEXT,
            reservation_expires_at TEXT,
            UNIQUE(company_id, list_id, phone_e164)
        )
    ");
    $pdo->exec("
        INSERT OR IGNORE INTO contacts_new (
            id, company_id, list_id, name, phone_raw, phone_e164, email, organization, city, state, product, origin,
            document, external_code, notes, custom_json, status, attempts, last_call_at, reserved_by, reserved_at, reservation_expires_at
        )
        SELECT
            id, company_id, list_id, name, phone_raw, phone_e164, email, organization, city, state, product, origin,
            document, external_code, notes, custom_json, status, attempts, last_call_at, reserved_by, reserved_at, reservation_expires_at
        FROM contacts
    ");
    $pdo->exec('DROP TABLE contacts');
    $pdo->exec('ALTER TABLE contacts_new RENAME TO contacts');
    $pdo->exec('PRAGMA foreign_keys = ON');
}

function seed(PDO $pdo): void
{
    ensure_default_plans($pdo);
    if ((int)$pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn() > 0) {
        upgrade_demo_data($pdo);
        return;
    }

    $pdo->prepare("INSERT INTO companies (legal_name, trade_name, cnpj, email, phone, max_users, max_agents, max_channels, monthly_minutes_limit)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute(['Escritorio de Consorcio Demo LTDA', 'Consultores Ademicon Demo', '00.000.000/0001-00', 'admin@consorciocall.local', '(41) 99999-0000', 25, 15, 5, 5000]);
    $companyId = (int)$pdo->lastInsertId();

    $users = [
        ['Administrador Geral', 'admin@consorciocall.local', 'admin123', 'admin_geral', null, '1000'],
        ['Gestor Comercial', 'gestor@consorciocall.local', 'admin123', 'admin_empresa', null, '1001'],
        ['Supervisor Comercial', 'supervisor@consorciocall.local', 'admin123', 'supervisor', null, '2001'],
        ['Ana Consultora', 'consultor@consorciocall.local', 'admin123', 'atendente', null, '3001'],
    ];
    $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password_hash, role, extension) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute([$companyId, $user[0], $user[1], password_hash($user[2], PASSWORD_DEFAULT), $user[3], $user[5]]);
    }

    $supervisorId = (int)$pdo->query("SELECT id FROM users WHERE role = 'supervisor'")->fetchColumn();
    $agentId = (int)$pdo->query("SELECT id FROM users WHERE role = 'atendente'")->fetchColumn();
    $pdo->prepare("INSERT INTO teams (company_id, name, description, supervisor_id, daily_goal, voip_queue) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$companyId, 'Consultores Ademicon', 'Operacao ativa para leads de consorcio', $supervisorId, 80, 'ademicon']);
    $teamId = (int)$pdo->lastInsertId();
    $pdo->prepare("UPDATE users SET team_id = ? WHERE id IN (?, ?)")->execute([$teamId, $supervisorId, $agentId]);
    $plan = one("SELECT * FROM plans WHERE name = 'MVP'");
    if ($plan) {
        $pdo->prepare("INSERT OR IGNORE INTO subscriptions (company_id, plan_id, plan_name, starts_at, renews_at, included_minutes, max_users, max_consultants, max_lists, max_contacts, commercial_price_per_minute, status)
            VALUES (?, ?, ?, date('now'), date('now', '+30 days'), ?, ?, ?, ?, ?, ?, 'Ativa')")
            ->execute([$companyId, $plan['id'], $plan['name'], $plan['included_minutes'], $plan['max_users'], $plan['max_consultants'], $plan['max_lists'], $plan['max_contacts'], $plan['commercial_price_per_minute']]);
    }
    $pdo->prepare("INSERT INTO consultant_profiles (company_id, user_id, team_id, display_name, internal_code, status, goal) VALUES (?, ?, ?, ?, ?, 'Ativo', ?)")
        ->execute([$companyId, $agentId, $teamId, 'Ana Consultora', 'consultor-principal', 80]);

    $pdo->prepare("INSERT INTO contact_lists (company_id, name, description, source, created_by) VALUES (?, ?, ?, ?, ?)")
        ->execute([$companyId, 'Leads consorcio julho', 'Lista de demonstracao para consultores de consorcio', 'Seed', 1]);
    $listId = (int)$pdo->lastInsertId();

    $contacts = [
        ['Joao da Silva', '(41) 99999-9999', '+5541999999999', 'joao@email.com', 'Curitiba', 'PR', 'Carta imovel 500k'],
        ['Maria Oliveira', '(11) 98888-8888', '+5511988888888', 'maria@email.com', 'Sao Paulo', 'SP', 'Carta auto 120k'],
        ['Carlos Pereira', '(48) 97777-7777', '+5548977777777', 'carlos@email.com', 'Florianopolis', 'SC', 'Carta investimento 300k'],
    ];
    $stmt = $pdo->prepare("INSERT INTO contacts (company_id, list_id, name, phone_raw, phone_e164, email, city, state, product, origin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($contacts as $contact) {
        $stmt->execute([$companyId, $listId, $contact[0], $contact[1], $contact[2], $contact[3], $contact[4], $contact[5], $contact[6], 'Demo']);
    }

    $pdo->prepare("INSERT INTO campaigns (company_id, list_id, team_id, supervisor_id, name, description, dialer_type, caller_id, script)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$companyId, $listId, $teamId, $supervisorId, 'Leads Ademicon - Consorcio', 'Consultores ligando para leads importados por CSV', 'preview', '+554130000000', "Confirme se o lead tem interesse em consorcio, identifique objetivo (imovel, auto ou investimento), valide prazo e valor aproximado da carta."]);

    $results = [
        ['Consorcio vendido', 'concluir'],
        ['Quer simulacao', 'concluir'],
        ['Enviar proposta', 'concluir'],
        ['Agendar retorno', 'agendar_retorno'],
        ['Sem interesse agora', 'concluir'],
        ['Ja comprou consorcio', 'concluir'],
        ['Numero incorreto', 'bloquear'],
        ['Nao atendeu', 'retornar_fila'],
        ['Ocupado', 'retornar_fila'],
        ['Caixa postal', 'retornar_fila'],
        ['Solicitou nao receber ligacoes', 'bloquear'],
        ['Outro', 'concluir'],
    ];
    $stmt = $pdo->prepare("INSERT INTO call_results (company_id, name, action, is_default) VALUES (?, ?, ?, 1)");
    foreach ($results as $result) {
        $stmt->execute([$companyId, $result[0], $result[1]]);
    }
}

function ensure_default_plans(PDO $pdo): void
{
    $plans = [
        ['MVP', 1000, 3, 3, 20, 5000, 0.35, 350000000, 350000, 299.00, 'Pix/Cartao'],
        ['Consultor Individual', 200, 1, 1, 10, 1000, 0.35, 70000000, 350000, 99.00, 'Pix/Cartao'],
        ['Escritorio com equipe', 1000, 5, 5, 50, 10000, 0.30, 300000000, 300000, 399.00, 'Boleto/Pix'],
    ];
    $insert = $pdo->prepare("INSERT OR IGNORE INTO plans (name, included_minutes, max_users, max_consultants, max_lists, max_contacts, commercial_price_per_minute, telephony_credit_micros, telephony_rate_micros, monthly_price, setup_fee, billing_period, payment_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'Mensal', ?, 'Ativo')");
    $backfill = $pdo->prepare("UPDATE plans SET monthly_price = CASE WHEN COALESCE(monthly_price, 0) <= 0 THEN ? ELSE monthly_price END, telephony_credit_micros = COALESCE(telephony_credit_micros, ?), telephony_rate_micros = COALESCE(telephony_rate_micros, ?) WHERE name = ?");
    foreach ($plans as $plan) {
        [$name, $minutes, $users, $consultants, $lists, $contacts, $rate, $creditMicros, $rateMicros, $monthlyPrice, $paymentType] = $plan;
        $insert->execute([$name, $minutes, $users, $consultants, $lists, $contacts, $rate, $creditMicros, $rateMicros, $monthlyPrice, $paymentType]);
        $backfill->execute([$monthlyPrice, $creditMicros, $rateMicros, $name]);
    }
}

function upgrade_demo_data(PDO $pdo): void
{
    ensure_default_plans($pdo);
    $pdo->exec("UPDATE companies SET legal_name = 'Escritorio de Consorcio Demo LTDA', trade_name = 'Consultores Ademicon Demo', email = 'admin@consorciocall.local', voip_provider = 'Nvoip' WHERE trade_name IN ('Calutec', 'Consultores Ademicon Demo')");
    $updates = [
        ['Administrador Geral', 'admin@consorciocall.local', 'admin_geral'],
        ['Gestor Comercial', 'gestor@consorciocall.local', 'admin_empresa'],
        ['Supervisor Comercial', 'supervisor@consorciocall.local', 'supervisor'],
        ['Ana Consultora', 'consultor@consorciocall.local', 'atendente'],
    ];
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE role = ?");
    foreach ($updates as $update) {
        try {
            $stmt->execute($update);
        } catch (Throwable) {
            // Existing custom users are preserved when a unique e-mail conflict exists.
        }
    }
    $pdo->exec("UPDATE teams SET name = 'Consultores Ademicon', description = 'Operacao ativa para leads de consorcio', voip_queue = 'ademicon' WHERE name = 'Equipe de Vendas'");
    $pdo->exec("UPDATE contact_lists SET name = 'Leads consorcio julho', description = 'Lista de demonstracao para consultores de consorcio' WHERE name = 'Leads iniciais'");
    $pdo->exec("UPDATE campaigns SET name = 'Leads Ademicon - Consorcio', description = 'Consultores ligando para leads importados por CSV', dialer_type = 'preview', sip_trunk = 'Nvoip', script = 'Confirme se o lead tem interesse em consorcio, identifique objetivo (imovel, auto ou investimento), valide prazo e valor aproximado da carta.' WHERE name = 'Campanha MVP'");
    $replacements = [
        'Venda realizada' => 'Consorcio vendido',
        'Cliente interessado' => 'Quer simulacao',
        'Nao interessado' => 'Sem interesse agora',
    ];
    $stmt = $pdo->prepare("UPDATE call_results SET name = ? WHERE name = ?");
    foreach ($replacements as $old => $new) {
        $stmt->execute([$new, $old]);
    }
    $plan = one("SELECT * FROM plans WHERE name = 'MVP'");
    foreach (rows('SELECT * FROM companies') as $company) {
        if (!one('SELECT id FROM subscriptions WHERE company_id = ?', [$company['id']]) && $plan) {
            $pdo->prepare("INSERT INTO subscriptions (company_id, plan_id, plan_name, starts_at, renews_at, included_minutes, max_users, max_consultants, max_lists, max_contacts, commercial_price_per_minute, status)
                VALUES (?, ?, ?, date('now'), date('now', '+30 days'), ?, ?, ?, ?, ?, ?, 'Ativa')")
                ->execute([$company['id'], $plan['id'], $plan['name'], $company['monthly_minutes_limit'] ?: $plan['included_minutes'], $company['max_users'] ?: $plan['max_users'], $company['max_agents'] ?: $plan['max_consultants'], $plan['max_lists'], $plan['max_contacts'], $plan['commercial_price_per_minute']]);
        }
        if (!one('SELECT id FROM teams WHERE company_id = ?', [$company['id']])) {
            $pdo->prepare("INSERT INTO teams (company_id, name, description, daily_goal, simultaneous_limit, priority, voip_queue) VALUES (?, 'Equipe principal', 'Equipe padrao criada automaticamente.', 0, 1, 1, 'principal')")
                ->execute([$company['id']]);
        }
        $teamId = (int)(one("SELECT id FROM teams WHERE company_id = ? ORDER BY id LIMIT 1", [$company['id']])['id'] ?? 0);
        foreach (rows("SELECT * FROM users WHERE company_id = ? AND role IN ('atendente','usuario_operacional')", [$company['id']]) as $consultantUser) {
            if (!one('SELECT id FROM consultant_profiles WHERE user_id = ?', [$consultantUser['id']])) {
                $pdo->prepare("INSERT INTO consultant_profiles (company_id, user_id, team_id, display_name, internal_code, status, goal) VALUES (?, ?, ?, ?, ?, 'Ativo', 0)")
                    ->execute([$company['id'], $consultantUser['id'], $consultantUser['team_id'] ?: $teamId, $consultantUser['name'], $consultantUser['extension'] ?: 'consultor-' . $consultantUser['id']]);
            }
        }
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function is_platform_admin(?array $user = null): bool
{
    $user ??= current_user();
    return in_array($user['role'] ?? '', ['admin_geral', 'admin_plataforma'], true);
}

function is_account_admin(?array $user = null): bool
{
    $user ??= current_user();
    return in_array($user['role'] ?? '', ['admin_geral', 'admin_plataforma', 'cliente_admin', 'admin_empresa'], true);
}

function user_has_accepted_current_terms(?array $user): bool
{
    return $user && hash_equals(TERMS_VERSION, (string)($user['terms_accepted_version'] ?? ''));
}

function is_consultant_user(?array $user = null): bool
{
    $user ??= current_user();
    return ($user['role'] ?? '') === 'atendente';
}

function scoped_calls_clause(string $alias, array $user): array
{
    [$clause, $params] = tenant_clause($alias);
    if (is_consultant_user($user)) {
        $clause .= ' AND ' . $alias . '.agent_id = ?';
        $params[] = (int)$user['id'];
    }
    return [$clause, $params];
}

function scoped_users_clause(string $alias, array $user): array
{
    [$clause, $params] = tenant_clause($alias);
    if (is_consultant_user($user)) {
        $clause .= ' AND ' . $alias . '.id = ?';
        $params[] = (int)$user['id'];
    }
    return [$clause, $params];
}

function ensure_call_results_for_company(int $companyId): void
{
    if ($companyId <= 0 || one('SELECT id FROM call_results WHERE company_id = ? LIMIT 1', [$companyId])) return;
    $defaults = [
        ['Consorcio vendido', 'concluir'],
        ['Quer simulacao', 'concluir'],
        ['Enviar proposta', 'concluir'],
        ['Agendar retorno', 'agendar_retorno'],
        ['Sem interesse agora', 'concluir'],
        ['Ja comprou consorcio', 'concluir'],
        ['Numero incorreto', 'bloquear'],
        ['Nao atendeu', 'retornar_fila'],
        ['Ocupado', 'retornar_fila'],
        ['Caixa postal', 'retornar_fila'],
        ['Solicitou nao receber ligacoes', 'bloquear'],
        ['Outro', 'concluir'],
    ];
    $stmt = db()->prepare('INSERT INTO call_results (company_id, name, action, is_default) VALUES (?, ?, ?, 1)');
    foreach ($defaults as [$name, $action]) $stmt->execute([$companyId, $name, $action]);
}

function access_modules(): array
{
    return [
        'dashboard' => 'Inicio',
        'companies' => 'Clientes',
        'plans' => 'Planos',
        'users' => 'Acessos',
        'lists' => 'Contatos e Listas',
        'campaigns' => 'Campanhas',
        'agent' => 'Discador',
        'supervisor' => 'Chamadas',
        'reports' => 'Relatorios',
        'recordings' => 'Gravacoes',
        'costs' => 'Plano e consumo',
        'settings' => 'Integracoes',
        'asterisk_diagnostics' => 'Diagnostico Asterisk',
        'radar' => 'Radar de Leads',
        'blocklist' => 'Bloqueio',
        'audit' => 'Auditoria',
        'account' => 'Minha conta',
    ];
}

function default_role_modules(string $role): array
{
    $matrix = [
        'admin_plataforma' => ['dashboard', 'companies', 'plans', 'users', 'lists', 'campaigns', 'radar', 'agent', 'supervisor', 'reports', 'recordings', 'costs', 'settings', 'asterisk_diagnostics', 'blocklist', 'audit'],
        'admin_geral' => ['dashboard', 'companies', 'plans', 'users', 'lists', 'campaigns', 'radar', 'agent', 'supervisor', 'reports', 'recordings', 'costs', 'settings', 'asterisk_diagnostics', 'blocklist', 'audit'],
        'cliente_admin' => ['dashboard', 'users', 'lists', 'campaigns', 'radar', 'agent', 'reports', 'recordings', 'costs', 'blocklist'],
        'admin_empresa' => ['dashboard', 'users', 'lists', 'campaigns', 'radar', 'agent', 'reports', 'recordings', 'costs', 'blocklist'],
        'usuario_operacional' => ['dashboard', 'lists', 'agent', 'reports', 'recordings'],
        'supervisor' => ['dashboard', 'lists', 'agent', 'reports', 'recordings', 'costs', 'blocklist'],
        'atendente' => ['dashboard', 'lists', 'agent', 'recordings'],
    ];
    $modules = $matrix[$role] ?? [];
    $modules[] = 'account';
    return array_values(array_unique($modules));
}

function access_profile_templates(): array
{
    return [
        'cliente_admin' => ['name' => 'Cliente admin', 'modules' => default_role_modules('cliente_admin')],
        'usuario_operacional' => ['name' => 'Usuario operacional', 'modules' => default_role_modules('usuario_operacional')],
        'supervisor' => ['name' => 'Supervisor', 'modules' => default_role_modules('supervisor')],
        'atendente' => ['name' => 'Consultor', 'modules' => default_role_modules('atendente')],
    ];
}

function ensure_default_access_profiles(PDO $pdo): void
{
    $companies = $pdo->query('SELECT id FROM companies')->fetchAll();
    foreach ($companies as $company) {
        foreach (access_profile_templates() as $roleKey => $template) {
            $exists = $pdo->prepare('SELECT id FROM access_profiles WHERE company_id = ? AND role_key = ? LIMIT 1');
            $exists->execute([$company['id'], $roleKey]);
            if ($exists->fetch()) {
                continue;
            }
            $pdo->prepare('INSERT INTO access_profiles (company_id, name, role_key, modules_json, created_by) VALUES (?, ?, ?, ?, NULL)')
                ->execute([$company['id'], $template['name'], $roleKey, selected_modules_json($template['modules'])]);
        }
    }
}

function sanitize_modules(mixed $modules): array
{
    $allowed = array_keys(access_modules());
    $modules = is_array($modules) ? $modules : [];
    $modules = array_map('strval', $modules);
    $modules[] = 'account';
    return array_values(array_unique(array_intersect($modules, $allowed)));
}

function selected_modules_json(mixed $modules): string
{
    return json_encode(sanitize_modules($modules), JSON_UNESCAPED_UNICODE);
}

function modules_from_json(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }
    $decoded = json_decode($json, true);
    return sanitize_modules(is_array($decoded) ? $decoded : []);
}

function modules_for_user(array $user): array
{
    if (is_platform_admin($user)) {
        return default_role_modules((string)$user['role']);
    }
    $custom = modules_from_json($user['allowed_modules_json'] ?? null);
    if ($custom) {
        return $custom;
    }
    $profileId = (int)($user['access_profile_id'] ?? 0);
    if ($profileId > 0) {
        $profile = one('SELECT modules_json FROM access_profiles WHERE id = ? AND (company_id = ? OR company_id IS NULL)', [$profileId, $user['company_id'] ?? 0]);
        if ($profile) {
            $modules = modules_from_json((string)$profile['modules_json']);
            if ($modules) {
                return $modules;
            }
        }
    }
    return default_role_modules((string)$user['role']);
}

function can(string $area): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    return in_array($area, modules_for_user($user), true);
}

function utf8_text(mixed $value): string
{
    $text = (string)$value;
    if ($text === '' || preg_match('//u', $text) === 1) {
        return $text;
    }
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
    }
    $converted = iconv('Windows-1252', 'UTF-8//IGNORE', $text);
    return $converted === false ? '' : $converted;
}

function h(?string $value): string
{
    return htmlspecialchars(utf8_text($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_encode_safe(mixed $value): string
{
    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    return $encoded === false ? '{"ok":false,"error":"Falha ao gerar resposta JSON."}' : $encoded;
}

function datetime_local(?string $value): string
{
    if (!$value) {
        return '';
    }
    return datetime_utc_display($value, 'Y-m-d\TH:i');
}

function local_datetime_to_utc_storage(string $value): string
{
    $value = trim($value);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(?::\d{2})?$/', $value)) {
        return '';
    }
    $normalized = str_replace('T', ' ', $value);
    if (strlen($normalized) === 16) {
        $normalized .= ':00';
    }
    try {
        $local = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $normalized, new DateTimeZone('America/Sao_Paulo'));
        $errors = DateTimeImmutable::getLastErrors();
        if (!$local || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $local->format('Y-m-d H:i:s') !== $normalized) {
            return '';
        }
        return $local->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    } catch (Throwable) {
        return '';
    }
}

function callback_datetime_storage(string $value): string
{
    return local_datetime_to_utc_storage($value);
}

function utc_now_storage(): string
{
    return gmdate('Y-m-d H:i:s');
}

function utc_storage_timestamp(?string $value): int|false
{
    $value = trim((string)$value);
    if ($value === '') {
        return false;
    }
    try {
        return (new DateTimeImmutable($value, new DateTimeZone('UTC')))->getTimestamp();
    } catch (Throwable) {
        return false;
    }
}

function sao_paulo_utc_period_bounds(string $period = 'day'): array
{
    $timezone = new DateTimeZone('America/Sao_Paulo');
    $now = new DateTimeImmutable('now', $timezone);
    $start = $period === 'month'
        ? $now->modify('first day of this month')->setTime(0, 0)
        : $now->setTime(0, 0);
    $end = $period === 'month' ? $start->modify('+1 month') : $start->modify('+1 day');
    $utc = new DateTimeZone('UTC');
    return [
        $start->setTimezone($utc)->format('Y-m-d H:i:s'),
        $end->setTimezone($utc)->format('Y-m-d H:i:s'),
    ];
}

function migrate_legacy_local_datetimes_to_utc(PDO $pdo): void
{
    foreach ([
        ['callbacks', 'scheduled_at'],
        ['campaigns', 'starts_at'],
        ['campaigns', 'ends_at'],
    ] as [$table, $column]) {
        $select = $pdo->query("SELECT id, {$column} value FROM {$table} WHERE {$column} IS NOT NULL AND trim({$column}) <> ''");
        $update = $pdo->prepare("UPDATE {$table} SET {$column} = ? WHERE id = ?");
        foreach ($select->fetchAll() as $row) {
            $utc = local_datetime_to_utc_storage((string)$row['value']);
            if ($utc !== '') {
                $update->execute([$utc, (int)$row['id']]);
            }
        }
    }
}

function money(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function monthly_usage(int $companyId, ?float $usedSeconds = null): array
{
    $company = one('SELECT * FROM companies WHERE id = ?', [$companyId]);
    $subscription = one('SELECT * FROM subscriptions WHERE company_id = ?', [$companyId]);
    $limit = (int)($subscription['included_minutes'] ?? 0);
    if ($limit <= 0) {
        $limit = (int)($company['monthly_minutes_limit'] ?? 0);
    }
    if ($usedSeconds === null) {
        [$monthStartUtc, $monthEndUtc] = sao_paulo_utc_period_bounds('month');
        $usedSeconds = (float)one("SELECT COALESCE(SUM(billable_seconds), 0) seconds FROM calls WHERE company_id = ? AND created_at >= ? AND created_at < ?", [$companyId, $monthStartUtc, $monthEndUtc])['seconds'];
    }
    $used = $usedSeconds / 60;
    $remaining = max(0, $limit - $used);
    $percent = $limit > 0 ? min(100, ($used / $limit) * 100) : 0;
    return [
        'limit' => round($limit, 1),
        'used' => round($used, 1),
        'remaining' => round($remaining, 1),
        'percent' => round($percent, 1),
    ];
}

function recent_phone_history(int $companyId, int $agentId): array
{
    $items = rows("
        SELECT 1 bucket_order, 'todas' bucket, item.* FROM (
            SELECT co.id, co.destination_number, co.origin_number, co.status, co.created_at, co.duration_seconds,
                   ct.name contato, ct.city, ct.state, cr.name resultado
            FROM calls co
            LEFT JOIN contacts ct ON ct.id = co.contact_id
            LEFT JOIN call_results cr ON cr.id = co.result_id
            WHERE co.company_id = ? AND co.agent_id = ?
            ORDER BY co.id DESC LIMIT 8
        ) item
        UNION ALL
        SELECT 2 bucket_order, 'recebidas' bucket, item.* FROM (
            SELECT co.id, co.destination_number, co.origin_number, co.status, co.created_at, co.duration_seconds,
                   ct.name contato, ct.city, ct.state, cr.name resultado
            FROM calls co
            LEFT JOIN contacts ct ON ct.id = co.contact_id
            LEFT JOIN call_results cr ON cr.id = co.result_id
            WHERE co.company_id = ? AND co.agent_id = ? AND co.status IN ('received','incoming','inbound')
            ORDER BY co.id DESC LIMIT 8
        ) item
        UNION ALL
        SELECT 3 bucket_order, 'realizadas' bucket, item.* FROM (
            SELECT co.id, co.destination_number, co.origin_number, co.status, co.created_at, co.duration_seconds,
                   ct.name contato, ct.city, ct.state, cr.name resultado
            FROM calls co
            LEFT JOIN contacts ct ON ct.id = co.contact_id
            LEFT JOIN call_results cr ON cr.id = co.result_id
            WHERE co.company_id = ? AND co.agent_id = ? AND co.status NOT IN ('received','incoming','inbound')
            ORDER BY co.id DESC LIMIT 8
        ) item
        UNION ALL
        SELECT 4 bucket_order, 'perdidas' bucket, item.* FROM (
            SELECT co.id, co.destination_number, co.origin_number, co.status, co.created_at, co.duration_seconds,
                   ct.name contato, ct.city, ct.state, cr.name resultado
            FROM calls co
            LEFT JOIN contacts ct ON ct.id = co.contact_id
            LEFT JOIN call_results cr ON cr.id = co.result_id
            WHERE co.company_id = ? AND co.agent_id = ?
              AND (co.status IN ('failed','cancelled','busy','no_answer','missed') OR cr.name IN ('Nao atendeu','Ocupado','Caixa postal'))
            ORDER BY co.id DESC LIMIT 8
        ) item
        ORDER BY bucket_order, id DESC
    ", array_merge(...array_fill(0, 4, [$companyId, $agentId])));

    $history = ['todas' => [], 'recebidas' => [], 'realizadas' => [], 'perdidas' => []];
    foreach ($items as $item) {
        $bucket = (string)$item['bucket'];
        unset($item['bucket'], $item['bucket_order']);
        $history[$bucket][] = $item;
    }
    return $history;
}

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : (string)$value;
}

function app_public_url(string $query = ''): string
{
    $base = rtrim(env_value('APP_URL', ''), '/');
    if ($base === '') {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
        $scriptDir = trim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'))), '/');
        $base = ($isHttps ? 'https' : 'http') . '://' . $host . ($scriptDir !== '' ? '/' . $scriptDir : '');
    }
    return $base . '/?' . ltrim($query, '?');
}

function price_per_minute(): float
{
    $config = nvoip_config();
    return (float)$config['price_per_minute'];
}

function call_plan_rate_micros(int $companyId): int
{
    $row = one("SELECT s.telephony_rate_micros, p.telephony_rate_micros plan_rate_micros
        FROM subscriptions s
        LEFT JOIN plans p ON p.id = s.plan_id
        WHERE s.company_id = ?
        ORDER BY s.id DESC LIMIT 1", [$companyId]);
    return $row && $row['telephony_rate_micros'] !== null
        ? max(0, (int)$row['telephony_rate_micros'])
        : max(0, (int)($row['plan_rate_micros'] ?? 0));
}

function call_billing_values(array $call, int $billableSeconds): array
{
    $storedRate = array_key_exists('billing_rate_micros', $call) && $call['billing_rate_micros'] !== null
        ? (int)$call['billing_rate_micros']
        : call_plan_rate_micros((int)$call['company_id']);
    return call_uses_directcall_tariff($call)
        ? billing_full_minute_call_cost($billableSeconds, $storedRate)
        : billing_proportional_call_cost($billableSeconds, $storedRate);
}

function call_uses_directcall_tariff(array $call): bool
{
    $mode = strtoupper(trim((string)($call['telephony_mode'] ?? '')));
    $trunk = strtolower(trim((string)($call['telephony_trunk'] ?? '')));
    if ($mode === 'ASTERISK' && ($trunk === 'directcall' || str_contains($trunk, 'directcall'))) {
        return true;
    }
    if ($mode !== '' || $trunk !== '') {
        return false;
    }
    $config = asterisk_config();
    return !empty($config['enabled'])
        && strtoupper((string)($config['active_mode'] ?? '')) === 'ASTERISK'
        && strtoupper((string)($config['active_route'] ?? '')) === 'DIRECTCALL_TRUNK';
}

function call_billable_seconds(array $call, int $fallbackDuration, bool $answered, mixed $providerBillable = null): int
{
    if ($providerBillable !== null && $providerBillable !== '' && is_numeric($providerBillable)) {
        return max(0, (int)$providerBillable);
    }
    if (!$answered) {
        return 0;
    }
    if (!empty($call['answered_at'])) {
        $answeredAt = utc_storage_timestamp((string)$call['answered_at']);
        $endedAt = !empty($call['ended_at']) ? utc_storage_timestamp((string)$call['ended_at']) : time();
        if ($answeredAt !== false && $endedAt !== false && $endedAt >= $answeredAt) {
            return $endedAt - $answeredAt;
        }
    }
    return max(0, $fallbackDuration);
}

function call_conversation_duration_seconds(array $call, ?int $endedAtFallback = null): int
{
    if (empty($call['answered_at'])) return 0;
    $answeredAt = utc_storage_timestamp((string)$call['answered_at']);
    $endedAt = !empty($call['ended_at'])
        ? utc_storage_timestamp((string)$call['ended_at'])
        : ($endedAtFallback ?? time());
    if ($answeredAt === false || $endedAt === false || $endedAt < $answeredAt) return 0;
    return $endedAt - $answeredAt;
}

function call_cost_sql(string $alias = 'co'): string
{
    return "COALESCE({$alias}.estimated_cost_micros, CAST(ROUND({$alias}.estimated_cost * 1000000) AS INTEGER), 0)";
}

function telephony_credit_state(int $companyId): array
{
    ensure_telephony_period_initialized($companyId);
    $subscription = one('SELECT * FROM subscriptions WHERE company_id = ?', [$companyId]) ?: [];
    $configured = !empty($subscription['telephony_period_id'])
        && $subscription['telephony_credit_initial_micros'] !== null
        && $subscription['telephony_rate_micros'] !== null;
    return [
        'configured' => $configured,
        'subscription' => $subscription,
        'period_id' => (int)($subscription['telephony_period_id'] ?? 0),
        'initial_micros' => max(0, (int)($subscription['telephony_credit_initial_micros'] ?? 0)),
        'rate_micros' => max(0, (int)($subscription['telephony_rate_micros'] ?? 0)),
        'balance_micros' => (int)($subscription['telephony_balance_micros'] ?? 0),
    ];
}

function mvp_test_telephony_allowed(int $companyId): bool
{
    $user = current_user();
    if (!$user || !is_platform_admin($user) || (int)$user['company_id'] !== $companyId) {
        return false;
    }
    $plan = one('SELECT p.name FROM subscriptions s LEFT JOIN plans p ON p.id = s.plan_id WHERE s.company_id = ?', [$companyId]) ?: [];
    return billing_mvp_test_call_allowed(true, (string)($plan['name'] ?? ''));
}

function telephony_call_allowed(int $companyId): array
{
    $state = telephony_credit_state($companyId);
    if (mvp_test_telephony_allowed($companyId)) {
        return ['ok' => true, 'state' => $state, 'test_mode' => true];
    }
    if (!$state['configured']) {
        return ['ok' => false, 'message' => 'Credito de telefonia nao configurado para o periodo atual.'];
    }
    if ($state['rate_micros'] <= 0) {
        return ['ok' => false, 'message' => 'Tarifa de telefonia nao configurada para o periodo atual.'];
    }
    if (!billing_telephony_call_allowed(true, $state['balance_micros'], $state['rate_micros'])) {
        return ['ok' => false, 'message' => 'Credito de telefonia insuficiente para iniciar uma chamada.'];
    }
    return ['ok' => true, 'state' => $state];
}

function telephony_record_call_debit(array $call, array $billing, ?int $responsibleUserId = null): void
{
    $costMicros = max(0, (int)($billing['cost_micros'] ?? 0));
    if ($costMicros <= 0) {
        return;
    }
    $pdo = db();
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $existing = one('SELECT id FROM telephony_ledger WHERE call_id = ?', [(int)$call['id']]);
        if ($existing) {
            $pdo->exec('COMMIT');
            return;
        }
        $subscription = one('SELECT * FROM subscriptions WHERE company_id = ?', [(int)$call['company_id']]);
        $periodId = (int)($call['telephony_period_id'] ?? 0) ?: (int)($subscription['telephony_period_id'] ?? 0);
        $period = $periodId ? one('SELECT * FROM subscription_periods WHERE id=? AND company_id=?', [$periodId, (int)$call['company_id']]) : null;
        if (!$subscription || !$period || $period['telephony_balance_micros'] === null) {
            $pdo->exec('COMMIT');
            return;
        }
        $before = (int)$period['telephony_balance_micros'];
        $after = billing_telephony_balance_after($before, $costMicros);
        $idempotency = 'call-debit:' . (int)$call['id'];
        $pdo->prepare('INSERT INTO telephony_ledger (company_id,subscription_id,subscription_period_id,call_id,entry_type,amount_micros,balance_before_micros,balance_after_micros,idempotency_key,reference_type,reference_id,responsible_user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([(int)$call['company_id'], (int)$subscription['id'], $periodId, (int)$call['id'], 'CALL_DEBIT', -$costMicros, $before, $after, $idempotency, 'call', (int)$call['id'], $responsibleUserId]);
        $pdo->prepare('UPDATE subscription_periods SET telephony_balance_micros=? WHERE id=?')->execute([$after, $periodId]);
        if ((int)($subscription['telephony_period_id'] ?? 0) === $periodId) {
            $pdo->prepare('UPDATE subscriptions SET telephony_balance_micros=? WHERE id=?')->execute([$after, (int)$subscription['id']]);
        }
        $pdo->exec('COMMIT');
    } catch (Throwable $e) {
        $pdo->exec('ROLLBACK');
        if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
            return;
        }
        throw $e;
    }
}

function telephony_manual_adjustment(int $companyId, int $responsibleUserId, int $amountMicros, string $entryType, string $notes): void
{
    if (!in_array($entryType, ['MANUAL_CREDIT', 'MANUAL_DEBIT', 'REFUND'], true) || $amountMicros <= 0) {
        throw new RuntimeException('Ajuste de credito invalido.');
    }
    $pdo = db();
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $state = telephony_credit_state($companyId);
        if (!$state['configured']) {
            throw new RuntimeException('O tenant nao possui um periodo de telefonia configurado.');
        }
        $signedAmount = $entryType === 'MANUAL_DEBIT' ? -$amountMicros : $amountMicros;
        $before = $state['balance_micros'];
        $after = $before + $signedAmount;
        if ($after < 0) {
            throw new RuntimeException('O ajuste de debito nao pode deixar o saldo de telefonia negativo.');
        }
        $idempotency = 'manual:' . $companyId . ':' . bin2hex(random_bytes(12));
        $pdo->prepare('INSERT INTO telephony_ledger (company_id,subscription_id,subscription_period_id,entry_type,amount_micros,balance_before_micros,balance_after_micros,idempotency_key,reference_type,notes,responsible_user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$companyId, (int)$state['subscription']['id'], $state['period_id'], $entryType, $signedAmount, $before, $after, $idempotency, 'manual_adjustment', $notes, $responsibleUserId]);
        $pdo->prepare('UPDATE subscriptions SET telephony_balance_micros=? WHERE id=?')->execute([$after, (int)$state['subscription']['id']]);
        $pdo->prepare('UPDATE subscription_periods SET telephony_balance_micros=? WHERE id=?')->execute([$after, $state['period_id']]);
        $pdo->exec('COMMIT');
        audit('ajustou_credito_telefonia', 'companies:' . $companyId, null, ['entry_type' => $entryType, 'amount_micros' => $amountMicros, 'notes' => $notes]);
    } catch (Throwable $e) {
        $pdo->exec('ROLLBACK');
        throw $e;
    }
}

function nvoip_enabled(): bool
{
    $config = nvoip_config();
    return strtolower((string)$config['mode']) === 'api' && (string)$config['api_url'] !== '';
}

function nvoip_api_url_error(string $url): ?string
{
    if ($url === '') {
        return 'Informe a URL da API de chamada da Nvoip.';
    }
    $host = strtolower((string)(parse_url($url, PHP_URL_HOST) ?: ''));
    if ($host === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        return 'A URL da API da Nvoip e invalida.';
    }
    $blockedHosts = ['www.postman.com', 'postman.com', 'www.nvoip.com.br', 'suporte.nvoip.com.br', 'painel.nvoip.com.br'];
    if (in_array($host, $blockedHosts, true)) {
        return 'Esta URL parece ser uma pagina de documentacao/painel, nao um endpoint REST. Use um endpoint da API, normalmente iniciando com https://api.nvoip.com.br/v2/...';
    }
    if ($host !== 'api.nvoip.com.br') {
        return 'Confira a URL: a API REST da Nvoip normalmente usa o host https://api.nvoip.com.br/v2/...';
    }
    return null;
}

function voip_status_label(): string
{
    return nvoip_enabled() ? 'Nvoip API configurada' : 'Modo demonstracao Nvoip';
}

function active_call_provider_label(): string
{
    $config = asterisk_config();
    if (empty($config['enabled']) || $config['active_mode'] !== 'ASTERISK') {
        return 'Nvoip';
    }
    return $config['active_route'] === 'DIRECTCALL_TRUNK' ? 'Direct Call' : 'Nvoip';
}

function nvoip_config(?int $companyId = null): array
{
    $user = current_user();
    $companyId = $companyId ?: (int)($user['company_id'] ?? 0);
    $defaults = [
        'mode' => env_value('NVOIP_MODE', 'simulate'),
        'auth_method' => env_value('NVOIP_AUTH_METHOD', 'napikey'),
        'api_url' => env_value('NVOIP_API_URL'),
        'napikey' => env_value('NVOIP_NAPIKEY'),
        'numbersip' => env_value('NVOIP_NUMBERSIP'),
        'user_sip' => env_value('NVOIP_USER_SIP'),
        'sip_wss_url' => env_value('NVOIP_SIP_WSS_URL', 'wss://app.nvoip.com.br:7443'),
        'sip_domain' => env_value('NVOIP_SIP_DOMAIN', 'app.nvoip.com.br'),
        'sip_password' => env_value('NVOIP_SIP_PASSWORD'),
        'auto_answer_nvoip_callback' => env_value('NVOIP_AUTO_ANSWER_CALLBACK', '0') === '1' ? 1 : 0,
        'sip_callback_timeout_seconds' => (int)env_value('NVOIP_SIP_CALLBACK_TIMEOUT', '60'),
        'user_token' => env_value('NVOIP_USER_TOKEN'),
        'payload_template' => env_value('NVOIP_PAYLOAD_TEMPLATE'),
        'origin_number' => env_value('NVOIP_ORIGIN', '+554130000000'),
        'price_per_minute' => (float)str_replace(',', '.', env_value('NVOIP_PRICE_PER_MINUTE', '0.06')),
        'recording_enabled' => env_value('NVOIP_RECORDING_ENABLED', '1') === '1' ? 1 : 0,
        'webhook_url' => env_value('NVOIP_WEBHOOK_URL', ''),
        'webhook_secret' => env_value('NVOIP_WEBHOOK_SECRET', ''),
    ];
    if (!$companyId) {
        return $defaults;
    }
    try {
        $row = one("SELECT DISTINCT i.*
            FROM integration_settings i
            JOIN users u ON u.company_id = i.company_id
            WHERE i.provider = 'nvoip'
              AND u.role IN ('admin_geral', 'admin_plataforma')
            ORDER BY i.id ASC LIMIT 1");
        if (!$row) {
            $row = one("SELECT * FROM integration_settings WHERE company_id = ? AND provider = 'nvoip'", [$companyId]);
        }
    } catch (Throwable) {
        return $defaults;
    }
    if (!$row) {
        return $defaults;
    }
    return [
        'mode' => $row['mode'] ?: $defaults['mode'],
        'auth_method' => $row['auth_method'] ?: $defaults['auth_method'],
        'api_url' => $row['api_url'] ?: $defaults['api_url'],
        'napikey' => $row['napikey'] ?: $defaults['napikey'],
        'numbersip' => $row['numbersip'] ?: $defaults['numbersip'],
        'user_sip' => $row['user_sip'] ?: $defaults['user_sip'],
        'sip_wss_url' => $row['sip_wss_url'] ?: $defaults['sip_wss_url'],
        'sip_domain' => $row['sip_domain'] ?: $defaults['sip_domain'],
        'sip_password' => $row['sip_password'] ? decrypt_secret((string)$row['sip_password']) : $defaults['sip_password'],
        'auto_answer_nvoip_callback' => (int)($row['auto_answer_nvoip_callback'] ?? $defaults['auto_answer_nvoip_callback']),
        'sip_callback_timeout_seconds' => (int)($row['sip_callback_timeout_seconds'] ?: $defaults['sip_callback_timeout_seconds']),
        'user_token' => $row['user_token'] ?: $defaults['user_token'],
        'payload_template' => $row['payload_template'] ?: $defaults['payload_template'],
        'origin_number' => $row['origin_number'] ?: $defaults['origin_number'],
        'price_per_minute' => (float)($row['price_per_minute'] ?: $defaults['price_per_minute']),
        'recording_enabled' => (int)$row['recording_enabled'],
        'webhook_url' => $row['webhook_url'] ?: $defaults['webhook_url'],
        'webhook_secret' => $row['webhook_secret'] ?: $defaults['webhook_secret'],
    ];
}

function blank_integration_config(): array
{
    return [
        'provider' => '',
        'integration_name' => '',
        'mode' => 'simulate',
        'auth_method' => 'napikey',
        'api_url' => '',
        'napikey' => '',
        'numbersip' => '',
        'user_sip' => '',
        'sip_wss_url' => 'wss://app.nvoip.com.br:7443',
        'sip_domain' => 'app.nvoip.com.br',
        'sip_password' => '',
        'auto_answer_nvoip_callback' => 0,
        'sip_callback_timeout_seconds' => 60,
        'user_token' => '',
        'payload_template' => '',
        'origin_number' => '',
        'price_per_minute' => '',
        'recording_enabled' => 0,
        'webhook_url' => '',
        'webhook_secret' => '',
        'extra_config' => '',
    ];
}

function integration_provider_key(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9_-]+/', '-', $value) ?: '';
    return trim($value, '-_');
}

function append_query_param(string $url, string $key, string $value): string
{
    $separator = str_contains($url, '?') ? '&' : '?';
    return $url . $separator . rawurlencode($key) . '=' . rawurlencode($value);
}

function nvoip_oauth_token(array $config): ?string
{
    if (($config['numbersip'] ?? '') === '' || ($config['user_token'] ?? '') === '') {
        return null;
    }
    $ch = curl_init('https://api.nvoip.com.br/v2/oauth/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
        CURLOPT_POSTFIELDS => http_build_query([
            'username' => $config['numbersip'],
            'password' => $config['user_token'],
            'grant_type' => 'password',
        ]),
        CURLOPT_TIMEOUT => 20,
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $status >= 400) {
        return null;
    }
    $json = json_decode((string)$body, true) ?: [];
    return isset($json['access_token']) ? (string)$json['access_token'] : null;
}

function render_payload_template(string $template, array $vars): ?array
{
    if (trim($template) === '') {
        return null;
    }
    $json = $template;
    foreach ($vars as $key => $value) {
        $json = str_replace('{{' . $key . '}}', (string)$value, $json);
    }
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : null;
}

function render_text_template(string $template, array $vars): string
{
    $text = $template;
    foreach ($vars as $key => $value) {
        $text = str_replace('{{' . $key . '}}', rawurlencode((string)$value), $text);
    }
    return $text;
}

function masked_secret(string $value): string
{
    if ($value === '') {
        return '';
    }
    return strlen($value) <= 8 ? str_repeat('*', strlen($value)) : substr($value, 0, 4) . str_repeat('*', max(4, strlen($value) - 8)) . substr($value, -4);
}

function secret_key(): string
{
    return hash('sha256', env_value('LIGFLOW_SECRET', __DIR__ . '|ligflow-local-secret'), true);
}

function encrypt_secret(string $value): string
{
    if ($value === '') {
        return '';
    }
    if (!function_exists('openssl_encrypt')) {
        return 'plain:' . base64_encode($value);
    }
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($value, 'aes-256-cbc', secret_key(), OPENSSL_RAW_DATA, $iv);
    return $encrypted === false ? '' : 'enc:' . base64_encode($iv . $encrypted);
}

function decrypt_secret(string $value): string
{
    if ($value === '') {
        return '';
    }
    if (str_starts_with($value, 'plain:')) {
        return (string)base64_decode(substr($value, 6), true);
    }
    if (!str_starts_with($value, 'enc:') || !function_exists('openssl_decrypt')) {
        return $value;
    }
    $raw = base64_decode(substr($value, 4), true);
    if ($raw === false || strlen($raw) <= 16) {
        return '';
    }
    $iv = substr($raw, 0, 16);
    $ciphertext = substr($raw, 16);
    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', secret_key(), OPENSSL_RAW_DATA, $iv);
    return $decrypted === false ? '' : $decrypted;
}

function google_places_config(): array
{
    $row = one('SELECT * FROM google_places_settings WHERE id = 1') ?: [];
    return [
        'active' => (int)($row['active'] ?? 0) === 1,
        'api_key' => !empty($row['api_key_encrypted'])
            ? decrypt_secret((string)$row['api_key_encrypted'])
            : env_value('GOOGLE_PLACES_API_KEY'),
    ];
}

function google_places_search(array $filters, string $pageToken = ''): array
{
    $config = google_places_config();
    if (!$config['active'] || $config['api_key'] === '') {
        throw new RuntimeException('Google Places API nao configurada ou inativa.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensao cURL do PHP e necessaria para consultar o Google Places.');
    }
    $queryParts = array_filter([
        trim((string)($filters['segment'] ?? '')),
        trim((string)($filters['street'] ?? '')),
        trim((string)($filters['neighborhood'] ?? '')),
        trim((string)($filters['city'] ?? '')),
        trim((string)($filters['state'] ?? '')),
        'Brasil',
    ]);
    if (trim((string)($filters['segment'] ?? '')) === '' || trim((string)($filters['city'] ?? '')) === '' || trim((string)($filters['state'] ?? '')) === '') {
        throw new RuntimeException('Informe segmento, cidade e estado para pesquisar empresas.');
    }
    $ch = curl_init('https://places.googleapis.com/v1/places:searchText');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $config['api_key'],
            'X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.websiteUri,places.rating,places.googleMapsUri,nextPageToken',
        ],
        CURLOPT_POSTFIELDS => json_encode(array_filter([
            'textQuery' => implode(', ', $queryParts),
            'pageSize' => 20,
            'languageCode' => 'pt-BR',
            'pageToken' => $pageToken ?: null,
        ], static fn($value) => $value !== null), JSON_UNESCAPED_UNICODE),
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $decoded = json_decode((string)$body, true);
    if ($body === false || $status >= 400 || !is_array($decoded)) {
        $message = (string)($decoded['error']['message'] ?? $error ?: 'Resposta invalida da Google Places API.');
        throw new RuntimeException($message);
    }
    $onlyWithPhone = !empty($filters['only_with_phone']);
    $places = [];
    foreach (($decoded['places'] ?? []) as $place) {
        $phone = normalize_phone((string)($place['nationalPhoneNumber'] ?? ''));
        if ($onlyWithPhone && !$phone) continue;
        $placeId = trim((string)($place['id'] ?? ''));
        if ($placeId === '') continue;
        $places[] = [
            'place_id' => $placeId,
            'name' => trim((string)($place['displayName']['text'] ?? 'Empresa sem nome')),
            'phone' => $phone ?: '',
            'address' => trim((string)($place['formattedAddress'] ?? '')),
            'website' => trim((string)($place['websiteUri'] ?? '')),
            'rating' => isset($place['rating']) ? (string)$place['rating'] : '-',
            'maps_url' => trim((string)($place['googleMapsUri'] ?? '')),
        ];
    }
    return ['places' => $places, 'next_page_token' => trim((string)($decoded['nextPageToken'] ?? ''))];
}

function google_places_search_pages(array $filters, string $pageToken = '', int $maxPages = 3, ?callable $fetchPage = null): array
{
    $maxPages = max(1, min(3, $maxPages));
    $fetchPage ??= static fn(array $pageFilters, string $token): array => google_places_search($pageFilters, $token);
    $places = [];
    $nextPageToken = $pageToken;
    $pagesFetched = 0;
    $pageError = '';

    for ($page = 0; $page < $maxPages; $page++) {
        try {
            $result = $fetchPage($filters, $nextPageToken);
        } catch (Throwable $e) {
            if ($pagesFetched === 0 && $pageToken === '') throw $e;
            $pageError = $e->getMessage();
            break;
        }
        $places = array_merge($places, (array)$result['places']);
        $pagesFetched++;
        $nextPageToken = trim((string)($result['next_page_token'] ?? ''));
        if ($nextPageToken === '') break;
    }

    if ($pagesFetched >= $maxPages && $pageError === '') {
        $nextPageToken = '';
    }
    return [
        'places' => $places,
        'next_page_token' => $nextPageToken,
        'pages_fetched' => $pagesFetched,
        'page_error' => $pageError,
    ];
}

function radar_duplicate_keys(int $companyId, array $places): array
{
    $placeIds = array_values(array_filter(array_unique(array_column($places, 'place_id'))));
    $phones = array_values(array_filter(array_unique(array_column($places, 'phone'))));
    $existingPlaces = [];
    $existingPhones = [];
    if ($placeIds) {
        $marks = implode(',', array_fill(0, count($placeIds), '?'));
        foreach (rows("SELECT external_code FROM contacts WHERE company_id = ? AND external_code IN ($marks)", array_merge([$companyId], array_map(static fn(string $id): string => 'google_place:' . $id, $placeIds))) as $row) {
            $existingPlaces[(string)$row['external_code']] = true;
        }
        foreach (rows("SELECT place_id FROM radar_lead_history WHERE company_id = ? AND list_id IS NOT NULL AND place_id IN ($marks)", array_merge([$companyId], $placeIds)) as $row) {
            $existingPlaces['google_place:' . (string)$row['place_id']] = true;
        }
    }
    if ($phones) {
        $marks = implode(',', array_fill(0, count($phones), '?'));
        foreach (rows("SELECT phone_e164 FROM contacts WHERE company_id = ? AND phone_e164 IN ($marks)", array_merge([$companyId], $phones)) as $row) {
            $existingPhones[(string)$row['phone_e164']] = true;
        }
        foreach (rows("SELECT phone_e164 FROM radar_lead_history WHERE company_id = ? AND list_id IS NOT NULL AND phone_e164 IN ($marks)", array_merge([$companyId], $phones)) as $row) {
            $existingPhones[(string)$row['phone_e164']] = true;
        }
    }
    return ['places' => $existingPlaces, 'phones' => $existingPhones];
}

function radar_filter_available_places(int $companyId, array $places, array $alreadyShown = []): array
{
    $duplicates = radar_duplicate_keys($companyId, array_merge($alreadyShown, $places));
    foreach ($alreadyShown as $place) {
        $placeId = trim((string)($place['place_id'] ?? ''));
        $phone = trim((string)($place['phone'] ?? ''));
        if ($placeId !== '') $duplicates['places']['google_place:' . $placeId] = true;
        if ($phone !== '') $duplicates['phones'][$phone] = true;
    }
    $new = [];
    $discarded = 0;
    foreach ($places as $place) {
        $placeKey = 'google_place:' . (string)$place['place_id'];
        $phone = trim((string)($place['phone'] ?? ''));
        if (isset($duplicates['places'][$placeKey]) || ($phone !== '' && isset($duplicates['phones'][$phone]))) {
            $discarded++;
            continue;
        }
        $new[] = $place;
        $duplicates['places'][$placeKey] = true;
        if ($phone !== '') $duplicates['phones'][$phone] = true;
    }
    return ['places' => $new, 'discarded' => $discarded];
}

function radar_add_places_to_list(int $companyId, int $userId, int $listId, array $places): int
{
    if (!$places || !$listId) return 0;
    $list = one('SELECT id FROM contact_lists WHERE id = ? AND company_id = ?', [$listId, $companyId]);
    if (!$list) throw new RuntimeException('Lista nao encontrada.');
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare("INSERT OR IGNORE INTO contacts (company_id,list_id,name,phone_raw,phone_e164,organization,origin,external_code,notes,status) VALUES (?,?,?,?,?,?,?,?,?,'novo')");
        $clearUnusedPhone = $pdo->prepare('DELETE FROM radar_lead_history WHERE company_id = ? AND list_id IS NULL AND phone_e164 = ? AND place_id <> ?');
        $saveHistory = $pdo->prepare("INSERT INTO radar_lead_history (company_id,list_id,place_id,phone_e164,search_json,created_by) VALUES (?,?,?,?,?,?)
            ON CONFLICT(company_id,place_id) DO UPDATE SET list_id=excluded.list_id,phone_e164=excluded.phone_e164,created_by=excluded.created_by");
        $added = 0;
        foreach ($places as $place) {
            $phone = trim((string)($place['phone'] ?? ''));
            if ($phone === '') continue;
            $insert->execute([$companyId, $listId, $place['name'], $phone, $phone, $place['name'], 'Google Places', 'google_place:' . $place['place_id'], trim((string)($place['address'] ?? ''))]);
            if ($insert->rowCount() !== 1) continue;
            $added++;
            $clearUnusedPhone->execute([$companyId, $phone, $place['place_id']]);
            $saveHistory->execute([$companyId, $listId, $place['place_id'], $phone, '{}', $userId]);
        }
        $pdo->commit();
        return $added;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function radar_session_places(array $placeIds): array
{
    $stored = $_SESSION['radar_leads'] ?? [];
    $wanted = array_fill_keys(array_filter(array_map('strval', $placeIds)), true);
    return array_values(array_filter((array)($stored['places'] ?? []), static fn(array $place): bool => isset($wanted[(string)($place['place_id'] ?? '')])));
}

interface TelephonyProvider
{
    public function mode(): string;
    public function trunk(): string;
    public function originate(array $campaign, array $contact, array $agent): array;
    public function hangup(array $call): void;
    public function health(): array;
}

function valid_asterisk_trunk_identifier(string $value): bool
{
    return preg_match('/^[A-Za-z0-9_-]+$/', $value) === 1;
}

function valid_asterisk_webrtc_domain(string $domain): bool
{
    return preg_match('/^[A-Za-z0-9.-]+$/', $domain) === 1 && !str_contains($domain, '..') && !str_starts_with($domain, '.') && !str_ends_with($domain, '.');
}
function valid_asterisk_webrtc_endpoint(string $endpoint): bool
{
    return preg_match('/^(?:PJSIP\\/)?[A-Za-z0-9_.-]+$/i', $endpoint) === 1;
}
function valid_asterisk_webrtc_wss_url(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $parts = parse_url($url);
    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    $host = strtolower((string)($parts['host'] ?? ''));
    if ($scheme === 'wss') return true;
    return $scheme === 'ws' && in_array($host, ['localhost', '127.0.0.1', '::1'], true);
}
function asterisk_config(): array
{
    $row = one('SELECT * FROM asterisk_settings WHERE id = 1') ?: [];
    $route = strtoupper((string)($row['active_route'] ?? 'NVOIP_TRUNK'));
    if (!in_array($route, ['NVOIP_TRUNK', 'DIRECTCALL_TRUNK'], true)) $route = 'NVOIP_TRUNK';
    $nvoipTrunk = trim((string)($row['nvoip_trunk'] ?? 'nvoip'));
    if ($nvoipTrunk === '' || strtoupper($nvoipTrunk) === 'NVOIP_TRUNK') $nvoipTrunk = 'nvoip';
    return [
        'enabled' => (int)($row['enabled'] ?? 0) === 1,
        'environment' => (string)($row['environment'] ?? 'test'),
        'active_mode' => strtoupper((string)($row['active_mode'] ?? 'NVOIP_DIRECT')),
        'active_route' => $route,
        'ari_url' => rtrim((string)($row['ari_url'] ?? env_value('ASTERISK_ARI_URL')), '/'),
        'ari_ws_url' => (string)($row['ari_ws_url'] ?? env_value('ASTERISK_ARI_WS_URL')),
        'ari_username' => (string)($row['ari_username'] ?? env_value('ASTERISK_ARI_USERNAME')),
        'ari_password' => !empty($row['ari_password_encrypted']) ? decrypt_secret((string)$row['ari_password_encrypted']) : env_value('ASTERISK_ARI_PASSWORD'),
        'stasis_app' => trim((string)($row['stasis_app'] ?? 'ligflow')) ?: 'ligflow',
        'originate_timeout_seconds' => max(5, (int)($row['originate_timeout_seconds'] ?? 30)),
        'bridge_timeout_seconds' => max(5, (int)($row['bridge_timeout_seconds'] ?? 15)),
        'reconnect_initial_seconds' => max(1, (int)($row['reconnect_initial_seconds'] ?? 2)),
        'reconnect_max_seconds' => max(2, (int)($row['reconnect_max_seconds'] ?? 30)),
        'sip_wss_url' => (string)($row['sip_wss_url'] ?? ''),
        'sip_domain' => (string)($row['sip_domain'] ?? ''),
        'consultant_endpoint' => trim((string)($row['consultant_endpoint'] ?? '')),
        'webrtc_password' => !empty($row['webrtc_password_encrypted']) ? decrypt_secret((string)$row['webrtc_password_encrypted']) : '',
        'webrtc_context' => trim((string)($row['webrtc_context'] ?? '')),
        'nvoip_trunk' => $nvoipTrunk,
        'directcall_trunk' => trim((string)($row['directcall_trunk'] ?? 'directcall')) ?: 'directcall',
        'extension_start' => (int)($row['extension_start'] ?? 1000),
        'extension_end' => (int)($row['extension_end'] ?? 9999),
        'provisioning_agent_url' => trim((string)($row['provisioning_agent_url'] ?? '')),
        'provisioning_agent_secret' => !empty($row['provisioning_agent_secret_encrypted']) ? decrypt_secret((string)$row['provisioning_agent_secret_encrypted']) : '',
        'provisioning_agent_timeout_seconds' => max(3, min(60, (int)($row['provisioning_agent_timeout_seconds'] ?? 10))),
    ];
}

function asterisk_ari_request(array $config, string $method, string $path, ?array $payload = null): array
{
    if (!function_exists('curl_init')) throw new RuntimeException('A extensao cURL e obrigatoria para a integracao Asterisk ARI.');
    if ($config['ari_url'] === '' || $config['ari_username'] === '' || $config['ari_password'] === '') {
        throw new RuntimeException('Configure URL, usuario e senha do ARI antes de usar o Asterisk.');
    }
    if (!filter_var($config['ari_url'], FILTER_VALIDATE_URL)) throw new RuntimeException('URL do ARI invalida.');
    $url = $config['ari_url'] . '/' . ltrim($path, '/');
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_USERPWD => $config['ari_username'] . ':' . $config['ari_password'],
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json'],
        CURLOPT_TIMEOUT => max(5, (int)$config['originate_timeout_seconds']),
    ]);
    if ($payload !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $decoded = json_decode((string)$body, true);
    if ($body === false || $status < 200 || $status >= 300) {
        $message = (string)($decoded['message'] ?? $error ?: 'Falha na requisicao ARI.');
        throw new RuntimeException('ARI: ' . $message);
    }
    return is_array($decoded) ? $decoded : [];
}

final class NvoipDirectProvider implements TelephonyProvider
{
    public function mode(): string { return 'NVOIP_DIRECT'; }
    public function trunk(): string { return 'NVOIP_DIRECT'; }
    public function originate(array $campaign, array $contact, array $agent): array
    {
        $result = make_nvoip_direct_call($campaign, $contact, $agent);
        $result['telephony_mode'] = $this->mode();
        $result['telephony_trunk'] = $this->trunk();
        return $result;
    }
    public function hangup(array $call): void { }
    public function health(): array { return ['server' => nvoip_enabled(), 'ari' => null, 'websocket' => null, 'webrtc' => null, 'trunks' => []]; }
}

final class AsteriskProvider implements TelephonyProvider
{
    private $ariRequest;
    public function __construct(private array $config, ?callable $ariRequest = null)
    {
        $this->ariRequest = $ariRequest ?? 'asterisk_ari_request';
    }
    public function mode(): string { return 'ASTERISK'; }
    public function trunk(): string { return $this->config['active_route']; }
    private function routeTrunk(): string
    {
        return $this->trunk() === 'DIRECTCALL_TRUNK' ? $this->config['directcall_trunk'] : $this->config['nvoip_trunk'];
    }
    private function safeEndpoint(string $endpoint): string
    {
        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $endpoint)) throw new RuntimeException('Endpoint Asterisk invalido.');
        return $endpoint;
    }
    public function createBridge(string $bridgeId): array
    {
        return asterisk_ari_request($this->config, 'POST', '/bridges/' . rawurlencode($bridgeId), ['type' => 'mixing', 'name' => 'LigFlow ' . $bridgeId]);
    }
    public function destroyBridge(string $bridgeId): void
    {
        if ($bridgeId !== '') asterisk_ari_request($this->config, 'DELETE', '/bridges/' . rawurlencode($bridgeId));
    }
    public function addChannelToBridge(string $bridgeId, string $channelId): void
    {
        if ($bridgeId !== '' && $channelId !== '') asterisk_ari_request($this->config, 'POST', '/bridges/' . rawurlencode($bridgeId) . '/addChannel', ['channel' => $channelId]);
    }
    public function recordBridge(string $bridgeId, string $recordingName, string $format = 'wav'): array
    {
        return asterisk_record_bridge_ari($this->ariRequest, $this->config, $bridgeId, $recordingName, $format);
    }
    public function connectConsultant(string $bridgeId, string $endpoint): array
    {
        $endpoint = $this->safeEndpoint($endpoint);
        return asterisk_ari_request($this->config, 'POST', '/channels', [
            'endpoint' => 'PJSIP/' . $endpoint,
            'app' => $this->config['stasis_app'],
            'appArgs' => 'ligflow,consultant,' . $bridgeId,
            'timeout' => $this->config['bridge_timeout_seconds'],
        ]);
    }
    public function originate(array $campaign, array $contact, array $agent): array
    {
        if (!$this->config['enabled']) throw new RuntimeException('Asterisk esta desabilitado.');
        $destination = nvoip_phone_digits((string)$contact['phone_e164']);
        if ($destination === '') throw new RuntimeException('Numero de destino invalido.');
        $trunk = $this->safeEndpoint($this->routeTrunk());
        $externalId = 'ARI-' . bin2hex(random_bytes(12));
        $bridgeId = 'ligflow-' . strtolower(bin2hex(random_bytes(8)));
        try {
            $this->createBridge($bridgeId);
            $channel = asterisk_ari_request($this->config, 'POST', '/channels', [
                'channelId' => $externalId,
                'endpoint' => 'PJSIP/' . $trunk . '/' . $destination,
                'app' => $this->config['stasis_app'],
                'appArgs' => 'ligflow,' . $externalId,
                'callerId' => nvoip_phone_digits((string)($campaign['caller_id'] ?? '')),
                'timeout' => $this->config['originate_timeout_seconds'],
                'variables' => ['LIGFLOW_EXTERNAL_ID' => $externalId, 'LIGFLOW_TRUNK' => $this->trunk()],
            ]);
            return [
                'ok' => true,
                'provider' => 'Asterisk ARI',
                'external_call_id' => $externalId,
                'provider_channel_id' => (string)($channel['id'] ?? $externalId),
                'provider_linked_id' => (string)($channel['connected']['id'] ?? ''),
                'provider_bridge_id' => $bridgeId,
                'telephony_mode' => $this->mode(),
                'telephony_trunk' => $this->trunk(),
                'status' => 'in_progress',
                'message' => 'Chamada enviada ao Asterisk pela rota ' . $this->trunk() . '.',
                'payload' => ['route' => $this->trunk(), 'channel_id' => $channel['id'] ?? $externalId, 'bridge_id' => $bridgeId],
            ];
        } catch (Throwable $e) {
            try { $this->destroyBridge($bridgeId); } catch (Throwable) { }
            throw $e;
        }
    }
    public function originateParallel(array $campaign, array $contact, array $agent, string $externalId): array
    {
        if (empty($this->config['enabled'])) throw new RuntimeException('Asterisk esta desabilitado.');
        $destination = nvoip_phone_digits((string)($contact['phone_e164'] ?? ''));
        if ($destination === '') throw new RuntimeException('Numero de destino invalido.');
        $trunk = $this->safeEndpoint($this->routeTrunk());
        $channel = asterisk_ari_request($this->config, 'POST', '/channels', [
            'channelId' => $externalId,
            'endpoint' => 'PJSIP/' . $trunk . '/' . $destination,
            'app' => $this->config['stasis_app'],
            'appArgs' => 'ligflow,' . $externalId,
            'callerId' => nvoip_phone_digits((string)($campaign['caller_id'] ?? '')),
            'timeout' => $this->config['originate_timeout_seconds'],
            'variables' => ['LIGFLOW_EXTERNAL_ID' => $externalId, 'LIGFLOW_TRUNK' => $this->trunk()],
        ]);
        return [
            'provider' => 'Asterisk ARI',
            'external_call_id' => $externalId,
            'provider_channel_id' => (string)($channel['id'] ?? $externalId),
            'provider_linked_id' => (string)($channel['connected']['id'] ?? ''),
            'telephony_mode' => $this->mode(),
            'telephony_trunk' => $this->trunk(),
        ];
    }

    public function connectParallelWinner(array $call, array $agent): array
    {
        $channelId = (string)($call['provider_channel_id'] ?? '');
        if ($channelId === '') throw new RuntimeException('Canal Asterisk indisponivel para conectar o consultor.');
        $bridgeId = 'ligflow-' . strtolower(bin2hex(random_bytes(8)));
        try {
            $this->createBridge($bridgeId);
            $this->addChannelToBridge($bridgeId, $channelId);
            $consultantEndpoint = preg_replace('/^PJSIP\//i', '', (string)($this->config['consultant_endpoint'] ?? ''));
            $consultant = $this->connectConsultant($bridgeId, (string)$consultantEndpoint);
            return ['bridge_id' => $bridgeId, 'consultant_channel_id' => (string)($consultant['id'] ?? '')];
        } catch (Throwable $e) {
            try { $this->destroyBridge($bridgeId); } catch (Throwable) { }
            throw $e;
        }
    }

    public function hangup(array $call): void
    {
        $channelId = (string)($call['provider_channel_id'] ?? $call['provider_call_id'] ?? '');
        if ($channelId !== '') asterisk_ari_request($this->config, 'DELETE', '/channels/' . rawurlencode($channelId));
        if (!empty($call['provider_bridge_id'])) $this->destroyBridge((string)$call['provider_bridge_id']);
    }
    public function health(): array
    {
        $health = ['server' => false, 'ari' => false, 'websocket' => $this->config['ari_ws_url'] !== '', 'webrtc' => $this->config['sip_wss_url'] !== '', 'trunks' => ['NVOIP_TRUNK' => false, 'DIRECTCALL_TRUNK' => false]];
        try {
            $health['server'] = true;
            asterisk_ari_request($this->config, 'GET', '/asterisk/info');
            $health['ari'] = true;
            foreach (['NVOIP_TRUNK' => $this->config['nvoip_trunk'], 'DIRECTCALL_TRUNK' => $this->config['directcall_trunk']] as $key => $trunk) {
                $health['trunks'][$key] = $trunk !== '';
            }
        } catch (Throwable) { }
        return $health;
    }
}

function telephony_provider_for_company(int $companyId): TelephonyProvider
{
    $config = asterisk_config();
    if ($config['enabled'] && $config['active_mode'] === 'ASTERISK') return new AsteriskProvider($config);
    return new NvoipDirectProvider();
}

function make_provider_call(array $campaign, array $contact, array $agent): array
{
    try {
        return telephony_provider_for_company((int)$campaign['company_id'])->originate($campaign, $contact, $agent);
    } catch (Throwable $e) {
        return ['ok' => false, 'provider' => 'Asterisk ARI', 'external_call_id' => '', 'telephony_mode' => 'ASTERISK', 'telephony_trunk' => asterisk_config()['active_route'], 'status' => 'failed', 'message' => $e->getMessage(), 'payload' => []];
    }
}

function make_nvoip_direct_call(array $campaign, array $contact, array $agent): array
{
    $externalId = 'NVOIP-DEMO-' . strtoupper(bin2hex(random_bytes(4)));
    $config = nvoip_config((int)$campaign['company_id']);
    $userSip = $config['user_sip'] ?: ($agent['extension'] ?? '') ?: ($config['numbersip'] ?? '');
    $originDigits = nvoip_phone_digits($config['origin_number'] ?: (string)$campaign['caller_id']);
    $destinationDigits = nvoip_phone_digits((string)$contact['phone_e164']);
    $vars = [
        'origin' => $originDigits,
        'destination' => $destinationDigits,
        'phone' => $destinationDigits,
        'destination_e164' => $contact['phone_e164'],
        'origin_e164' => $config['origin_number'] ?: (string)$campaign['caller_id'],
        'agent_extension' => $agent['extension'] ?? '',
        'agent_name' => $agent['name'] ?? '',
        'user_sip' => $userSip,
        'numbersip' => $config['numbersip'] ?? '',
        'record' => (int)$config['recording_enabled'] === 1 ? 'true' : 'false',
        'callback_url' => $config['webhook_url'],
    ];
    $payload = [
        'caller' => $userSip,
        'called' => $vars['destination'],
        'bina' => $vars['origin'],
    ];
    $customPayload = render_payload_template((string)($config['payload_template'] ?? ''), $vars);
    if ($customPayload !== null) {
        $payload = $customPayload;
    }

    if (!nvoip_enabled()) {
        return [
            'ok' => true,
            'provider' => 'Nvoip Demo',
            'external_call_id' => $externalId,
            'status' => 'in_progress',
            'message' => 'Chamada demonstrativa criada para teste. No discador principal, a ligacao sai pelo webfone SIP do navegador.',
            'payload' => $payload,
        ];
    }

    $renderedApiUrl = render_text_template((string)$config['api_url'], $vars);
    $urlError = nvoip_api_url_error($renderedApiUrl);
    if ($urlError !== null) {
        return [
            'ok' => false,
            'provider' => 'Nvoip',
            'external_call_id' => $externalId,
            'status' => 'failed',
            'message' => $urlError,
            'payload' => ['request' => $payload, 'api_url' => $config['api_url']],
        ];
    }

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    $apiUrl = $renderedApiUrl;
    if (($config['auth_method'] ?? 'napikey') === 'oauth') {
        $token = nvoip_oauth_token($config);
        if ($token === null) {
            return [
                'ok' => false,
                'provider' => 'Nvoip',
                'external_call_id' => $externalId,
                'status' => 'failed',
                'message' => 'Falha ao autenticar na Nvoip via OAuth. Confira NumberSIP e User Token.',
                'payload' => ['request' => $payload, 'auth_method' => 'oauth'],
            ];
        }
        $headers[] = 'Authorization: Bearer ' . $token;
    } elseif ($config['napikey'] !== '') {
        $apiUrl = append_query_param($apiUrl, 'napikey', (string)$config['napikey']);
        $headers[] = 'napikey: ' . $config['napikey'];
        $headers[] = 'Napikey: ' . $config['napikey'];
    } elseif (($config['auth_method'] ?? '') === 'bearer' && ($config['user_token'] ?? '') !== '') {
        $headers[] = 'Authorization: Bearer ' . $config['user_token'];
    }

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20,
    ]);
    $body = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $status >= 400) {
        log_call_status((int)$campaign['company_id'], null, 'Nvoip', 'failed', 'request_error', ['request' => $payload, 'response' => $body, 'http_status' => $status, 'auth_method' => $config['auth_method'] ?? 'napikey']);
        return [
            'ok' => false,
            'provider' => 'Nvoip',
            'external_call_id' => $externalId,
            'status' => 'failed',
            'message' => 'Falha ao solicitar chamada na Nvoip: ' . ($error ?: 'HTTP ' . $status . nvoip_error_hint($status, (string)$body)),
            'payload' => ['request' => $payload, 'response' => $body, 'http_status' => $status, 'auth_method' => $config['auth_method'] ?? 'napikey'],
        ];
    }

    $json = json_decode((string)$body, true) ?: [];
    if (stripos((string)$body, '<html') !== false || stripos((string)$body, 'Just a moment') !== false) {
        log_call_status((int)$campaign['company_id'], null, 'Nvoip', 'failed', 'html_response', ['request' => $payload, 'response' => substr((string)$body, 0, 300)]);
        return [
            'ok' => false,
            'provider' => 'Nvoip',
            'external_call_id' => $externalId,
            'status' => 'failed',
            'message' => 'A URL configurada retornou HTML/protecao de navegador, nao JSON da API. Use o endpoint REST da Nvoip em https://api.nvoip.com.br/v2/...',
            'payload' => ['request' => $payload, 'response' => substr((string)$body, 0, 300)],
        ];
    }
    $state = normalize_call_status((string)($json['state'] ?? 'in_progress'));
    if (!in_array($state, ['in_progress', 'calling_origin', 'ringing'], true)) {
        log_call_status((int)$campaign['company_id'], null, 'Nvoip', $state, 'discarded_non_calling_state', ['request' => $payload, 'response' => $json, 'http_status' => $status, 'state' => $state]);
        return [
            'ok' => false,
            'provider' => 'Nvoip',
            'external_call_id' => (string)($json['callId'] ?? $json['id'] ?? $json['call_id'] ?? $json['uuid'] ?? $externalId),
            'status' => 'failed',
            'message' => 'Estado da chamada nao entrou em modo de discagem (' . $state . '). Chamadas que nao estejam chamando sao descartadas para seguir o proximo numero.',
            'payload' => ['request' => $payload, 'response' => $json, 'http_status' => $status, 'state' => $state],
        ];
    }
    return [
        'ok' => true,
        'provider' => 'Nvoip',
        'external_call_id' => (string)($json['callId'] ?? $json['id'] ?? $json['call_id'] ?? $json['uuid'] ?? $externalId),
        'status' => $state,
        'message' => 'Chamada enviada para a Nvoip via API legada. No discador principal, prefira o webfone SIP direto do navegador.',
        'payload' => ['request' => $payload, 'response' => $json, 'http_status' => $status],
    ];
}

function mercado_pago_config(): array
{
    $row = one('SELECT * FROM payment_settings WHERE id = 1') ?: [];
    return [
        'active' => (int)($row['active'] ?? 0) === 1,
        'environment' => (string)($row['environment'] ?? 'test'),
        'public_key' => (string)($row['public_key'] ?? env_value('MERCADO_PAGO_PUBLIC_KEY')),
        'access_token' => !empty($row['access_token_encrypted']) ? decrypt_secret((string)$row['access_token_encrypted']) : env_value('MERCADO_PAGO_ACCESS_TOKEN'),
        'webhook_secret' => !empty($row['webhook_secret_encrypted']) ? decrypt_secret((string)$row['webhook_secret_encrypted']) : env_value('MERCADO_PAGO_WEBHOOK_SECRET'),
        'pix_enabled' => (int)($row['pix_enabled'] ?? 1) === 1,
        'card_enabled' => (int)($row['card_enabled'] ?? 1) === 1,
        'boleto_enabled' => (int)($row['boleto_enabled'] ?? 1) === 1,
    ];
}

function mercado_pago_request(string $method, string $path, ?array $payload = null, ?string $idempotencyKey = null): array
{
    $config = mercado_pago_config();
    if (!$config['active'] || $config['access_token'] === '') {
        throw new RuntimeException('Mercado Pago nao configurado ou inativo.');
    }
    $headers = ['Authorization: Bearer ' . $config['access_token'], 'Accept: application/json', 'Content-Type: application/json'];
    if ($idempotencyKey) {
        $headers[] = 'X-Idempotency-Key: ' . $idempotencyKey;
    }
    $ch = curl_init('https://api.mercadopago.com' . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 30]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $decoded = json_decode((string)$body, true);
    if ($body === false || $status >= 400 || !is_array($decoded)) {
        $message = (string)($decoded['message'] ?? $decoded['error'] ?? $error ?: 'Resposta invalida do Mercado Pago.');
        throw new RuntimeException($message);
    }
    return $decoded;
}

function tenant_billing_state(int $companyId, ?DateTimeImmutable $now = null): array
{
    $company = one('SELECT timezone FROM companies WHERE id = ?', [$companyId]) ?: [];
    $subscription = one('SELECT * FROM subscriptions WHERE company_id = ?', [$companyId]) ?: [];
    return billing_status_at($subscription['renews_at'] ?? null, (string)($company['timezone'] ?? 'America/Sao_Paulo'), $now) + ['subscription' => $subscription];
}

function telephony_plan_snapshot(array $plan): array
{
    if (!array_key_exists('telephony_credit_micros', $plan) || $plan['telephony_credit_micros'] === null
        || !array_key_exists('telephony_rate_micros', $plan) || $plan['telephony_rate_micros'] === null) {
        throw new RuntimeException('Configure o credito e a tarifa de telefonia do plano antes de utiliza-lo.');
    }
    return [
        'telephony_credit_initial_micros' => max(0, (int)$plan['telephony_credit_micros']),
        'telephony_rate_micros' => max(0, (int)$plan['telephony_rate_micros']),
    ];
}

function ensure_telephony_period_initialized(int $companyId): void
{
    $pdo = db();
    $ownTransaction = !$pdo->inTransaction();
    if ($ownTransaction) {
        $pdo->exec('BEGIN IMMEDIATE');
    }
    try {
        $subscription = one('SELECT * FROM subscriptions WHERE company_id = ?', [$companyId]);
        if (!$subscription || empty($subscription['plan_id']) || !empty($subscription['telephony_period_id'])) {
            if ($ownTransaction) $pdo->exec('COMMIT');
            return;
        }
        $plan = one('SELECT * FROM plans WHERE id = ?', [(int)$subscription['plan_id']]);
        if (!$plan) {
            if ($ownTransaction) $pdo->exec('COMMIT');
            return;
        }
        try {
            $telephonySnapshot = telephony_plan_snapshot($plan);
        } catch (RuntimeException $e) {
            if ($ownTransaction) $pdo->exec('COMMIT');
            return;
        }

        $paymentId = -(int)$subscription['id'];
        $period = one('SELECT * FROM subscription_periods WHERE payment_id = ? AND subscription_id = ?', [$paymentId, (int)$subscription['id']]);
        $timezone = new DateTimeZone((string)((one('SELECT timezone FROM companies WHERE id = ?', [$companyId]) ?: [])['timezone'] ?? 'America/Sao_Paulo'));
        if ($period) {
            $start = new DateTimeImmutable((string)$period['starts_at'], $timezone);
            $end = new DateTimeImmutable((string)$period['ends_at'], $timezone);
        } else {
            $now = new DateTimeImmutable('now', $timezone);
            $startValue = (string)($subscription['starts_at'] ?? '');
            $endValue = (string)($subscription['renews_at'] ?? '');
            $start = $startValue !== '' ? new DateTimeImmutable($startValue, $timezone) : $now;
            $end = $endValue !== '' ? new DateTimeImmutable($endValue, $timezone) : billing_period_end($start, (string)($plan['billing_period'] ?? 'Mensal'));
            if ($end <= $start) {
                $end = billing_period_end($start, (string)($plan['billing_period'] ?? 'Mensal'));
            }
            $limitsSnapshot = json_encode([
                'included_minutes' => (int)($subscription['included_minutes'] ?? $plan['included_minutes'] ?? 0),
                'max_users' => (int)($subscription['max_users'] ?? $plan['max_users'] ?? 1),
                'max_consultants' => (int)($subscription['max_consultants'] ?? $plan['max_consultants'] ?? 1),
                'max_lists' => (int)($subscription['max_lists'] ?? $plan['max_lists'] ?? 10),
                'max_contacts' => (int)($subscription['max_contacts'] ?? $plan['max_contacts'] ?? 1000),
                'commercial_price_per_minute' => (float)($subscription['commercial_price_per_minute'] ?? $plan['commercial_price_per_minute'] ?? 0),
                'telephony_credit_initial_micros' => $telephonySnapshot['telephony_credit_initial_micros'],
                'telephony_rate_micros' => $telephonySnapshot['telephony_rate_micros'],
            ]);
            $pdo->prepare('INSERT OR IGNORE INTO subscription_periods (company_id, subscription_id, plan_id, payment_id, starts_at, ends_at, limits_snapshot_json, telephony_credit_initial_micros, telephony_rate_micros, telephony_balance_micros) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$companyId, (int)$subscription['id'], (int)$subscription['plan_id'], $paymentId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'), $limitsSnapshot, $telephonySnapshot['telephony_credit_initial_micros'], $telephonySnapshot['telephony_rate_micros'], $telephonySnapshot['telephony_credit_initial_micros']]);
            $period = one('SELECT * FROM subscription_periods WHERE payment_id = ? AND subscription_id = ?', [$paymentId, (int)$subscription['id']]);
        }
        if ($period) {
            $periodId = (int)$period['id'];
            $initialMicros = (int)($period['telephony_credit_initial_micros'] ?? $telephonySnapshot['telephony_credit_initial_micros']);
            $rateMicros = (int)($period['telephony_rate_micros'] ?? $telephonySnapshot['telephony_rate_micros']);
            $balanceMicros = (int)($period['telephony_balance_micros'] ?? $initialMicros);
            $pdo->prepare('UPDATE subscriptions SET starts_at=COALESCE(NULLIF(starts_at, \'\'), ?), renews_at=COALESCE(NULLIF(renews_at, \'\'), ?), telephony_period_id=?, telephony_credit_initial_micros=?, telephony_rate_micros=?, telephony_balance_micros=? WHERE id=? AND telephony_period_id IS NULL')
                ->execute([$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'), $periodId, $initialMicros, $rateMicros, $balanceMicros, (int)$subscription['id']]);
            $pdo->prepare('INSERT OR IGNORE INTO telephony_ledger (company_id,subscription_id,subscription_period_id,entry_type,amount_micros,balance_before_micros,balance_after_micros,idempotency_key,reference_type,reference_id) VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$companyId, (int)$subscription['id'], $periodId, 'INITIAL_CREDIT', $initialMicros, 0, $initialMicros, 'period-credit:' . $periodId, 'subscription_period', $periodId]);
        }
        if ($ownTransaction) $pdo->exec('COMMIT');
    } catch (Throwable $e) {
        if ($ownTransaction && $pdo->inTransaction()) $pdo->exec('ROLLBACK');
        throw $e;
    }
}

function apply_approved_payment(int $paymentId, array $providerPayment): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $payment = one('SELECT * FROM payments WHERE id = ?', [$paymentId]);
        if (!$payment) {
            $pdo->commit();
            return;
        }
        $subscription = one('SELECT * FROM subscriptions WHERE company_id = ?', [$payment['company_id']]);
        $plan = one('SELECT * FROM plans WHERE id = ?', [$payment['plan_id']]);
        if (!$subscription || !$plan) {
            throw new RuntimeException('Assinatura ou plano nao encontrado.');
        }
        $company = one('SELECT timezone FROM companies WHERE id = ?', [$payment['company_id']]) ?: [];
        $timezone = new DateTimeZone((string)($company['timezone'] ?? 'America/Sao_Paulo'));
        $approvedValue = (string)($providerPayment['date_approved'] ?? $payment['approved_at'] ?? '');
        $approvedUtc = payment_datetime_to_utc($approvedValue) ?? gmdate('Y-m-d H:i:s');
        $approvedInstant = new DateTimeImmutable($approvedUtc, new DateTimeZone('UTC'));
        $approvedAt = $approvedInstant->setTimezone($timezone);
        $period = one('SELECT * FROM subscription_periods WHERE payment_id = ?', [$paymentId]);
        $createdPeriod = !$period;
        $snapshot = json_decode((string)$payment['limits_snapshot_json'], true) ?: [];
        $telephonySnapshot = [
            'telephony_credit_initial_micros' => array_key_exists('telephony_credit_initial_micros', $snapshot)
                ? (int)$snapshot['telephony_credit_initial_micros']
                : (int)(telephony_plan_snapshot($plan)['telephony_credit_initial_micros'] ?? 0),
            'telephony_rate_micros' => array_key_exists('telephony_rate_micros', $snapshot)
                ? (int)$snapshot['telephony_rate_micros']
                : (int)(telephony_plan_snapshot($plan)['telephony_rate_micros'] ?? 0),
        ];
        if ($period) {
            $start = new DateTimeImmutable((string)$period['starts_at'], $timezone);
            $end = new DateTimeImmutable((string)$period['ends_at'], $timezone);
            $telephonySnapshot['telephony_credit_initial_micros'] = (int)($period['telephony_credit_initial_micros'] ?? $telephonySnapshot['telephony_credit_initial_micros']);
            $telephonySnapshot['telephony_rate_micros'] = (int)($period['telephony_rate_micros'] ?? $telephonySnapshot['telephony_rate_micros']);
        } else {
            $currentEnd = !empty($subscription['renews_at']) ? new DateTimeImmutable((string)$subscription['renews_at'], $timezone) : $approvedAt;
            $start = $currentEnd > $approvedAt ? $currentEnd : $approvedAt;
            $end = billing_period_end($start, (string)$payment['billing_period']);
            $pdo->prepare('INSERT INTO subscription_periods (company_id, subscription_id, plan_id, payment_id, starts_at, ends_at, limits_snapshot_json, telephony_credit_initial_micros, telephony_rate_micros, telephony_balance_micros) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$payment['company_id'], $subscription['id'], $payment['plan_id'], $paymentId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'), $payment['limits_snapshot_json'], $telephonySnapshot['telephony_credit_initial_micros'], $telephonySnapshot['telephony_rate_micros'], $telephonySnapshot['telephony_credit_initial_micros']]);
            $period = one('SELECT * FROM subscription_periods WHERE payment_id = ?', [$paymentId]);
        }
        $periodId = (int)($period['id'] ?? 0);
        $periodBalance = (int)($period['telephony_balance_micros'] ?? $telephonySnapshot['telephony_credit_initial_micros']);
        $pdo->prepare("UPDATE subscriptions SET plan_id=?, plan_name=?, starts_at=?, renews_at=?, included_minutes=?, max_users=?, max_consultants=?, max_lists=?, max_contacts=?, commercial_price_per_minute=?, telephony_period_id=?, telephony_credit_initial_micros=?, telephony_rate_micros=?, telephony_balance_micros=?, status='Ativa' WHERE id=?")
            ->execute([$plan['id'], $plan['name'], $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'), $snapshot['included_minutes'] ?? 0, $snapshot['max_users'] ?? 1, $snapshot['max_consultants'] ?? 1, $snapshot['max_lists'] ?? 10, $snapshot['max_contacts'] ?? 1000, $snapshot['commercial_price_per_minute'] ?? 0, $periodId, $telephonySnapshot['telephony_credit_initial_micros'], $telephonySnapshot['telephony_rate_micros'], $periodBalance, $subscription['id']]);
        if (!$period || !$periodId) {
            throw new RuntimeException('Nao foi possivel criar o periodo de telefonia.');
        }
        $grantKey = 'period-credit:' . $periodId;
        $pdo->prepare('INSERT OR IGNORE INTO telephony_ledger (company_id,subscription_id,subscription_period_id,entry_type,amount_micros,balance_before_micros,balance_after_micros,idempotency_key,reference_type,reference_id,responsible_user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([(int)$payment['company_id'], (int)$subscription['id'], $periodId, 'INITIAL_CREDIT', $telephonySnapshot['telephony_credit_initial_micros'], 0, $telephonySnapshot['telephony_credit_initial_micros'], $grantKey, 'subscription_period', $periodId, (int)$payment['user_id']]);
        $pdo->prepare("UPDATE payments SET status='APPROVED', approved_at=?, updated_at=CURRENT_TIMESTAMP WHERE id=?")
            ->execute([$approvedInstant->format('Y-m-d H:i:s'), $paymentId]);
        if ($createdPeriod) {
            $pdo->prepare("INSERT INTO payment_events (company_id,payment_id,event_name,payload_json) VALUES (?,?,'PAYMENT_APPROVED',?)")
                ->execute([$payment['company_id'], $paymentId, json_encode(['renews_at' => $end->format(DATE_ATOM)])]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function sync_mercado_pago_payment(string $providerId): array
{
    $remote = mercado_pago_request('GET', '/v1/payments/' . rawurlencode($providerId));
    $payment = one("SELECT * FROM payments WHERE provider='mercado_pago' AND provider_payment_id=?", [$providerId]);
    if (!$payment) throw new RuntimeException('Pagamento local nao encontrado.');
    $internal = billing_normalize_payment_status((string)($remote['status'] ?? ''), (string)($remote['status_detail'] ?? ''));
    $approvedAtUtc = payment_datetime_to_utc($remote['date_approved'] ?? null);
    $expiresAtUtc = payment_datetime_to_utc($remote['date_of_expiration'] ?? null);
    db()->prepare('UPDATE payments SET status=?, provider_status=?, provider_status_detail=?, approved_at=COALESCE(?,approved_at), expires_at=COALESCE(?,expires_at), updated_at=CURRENT_TIMESTAMP WHERE id=?')
        ->execute([$internal, $remote['status'] ?? null, $remote['status_detail'] ?? null, $approvedAtUtc, $expiresAtUtc, $payment['id']]);
    if ($internal === 'APPROVED') apply_approved_payment((int)$payment['id'], $remote);
    return one('SELECT * FROM payments WHERE id=?', [$payment['id']]) ?: [];
}

function create_tenant_payment(int $companyId, int $userId, string $method, array $input): array
{
    $subscription = one('SELECT * FROM subscriptions WHERE company_id=?', [$companyId]);
    $plan = $subscription ? one('SELECT * FROM plans WHERE id=? AND status=?', [$subscription['plan_id'], 'Ativo']) : null;
    if (!$subscription || !$plan) throw new RuntimeException('Plano ativo do tenant nao encontrado.');
    $telephonySnapshot = telephony_plan_snapshot($plan);
    $config = mercado_pago_config();
    $enabled = ['pix' => $config['pix_enabled'], 'card' => $config['card_enabled'], 'boleto' => $config['boleto_enabled']];
    if (!isset($enabled[$method]) || !$enabled[$method]) throw new RuntimeException('Metodo de pagamento indisponivel.');
    $amount = billing_authoritative_amount($plan);
    if ($amount <= 0) throw new RuntimeException('Plano sem valor configurado.');
    $snapshot = array_intersect_key($subscription, array_flip(['included_minutes','max_users','max_consultants','max_lists','max_contacts','commercial_price_per_minute']));
    $snapshot['telephony_credit_initial_micros'] = $telephonySnapshot['telephony_credit_initial_micros'];
    $snapshot['telephony_rate_micros'] = $telephonySnapshot['telephony_rate_micros'];
    $reference = 'LF-' . $companyId . '-' . bin2hex(random_bytes(8));
    $idempotency = bin2hex(random_bytes(16));
    db()->prepare("INSERT INTO payments (company_id,user_id,plan_id,amount,billing_period,limits_snapshot_json,internal_reference,idempotency_key,payment_method,status) VALUES (?,?,?,?,?,?,?,?,?,'CREATED')")
        ->execute([$companyId,$userId,$plan['id'],$amount,$plan['billing_period'] ?: 'Mensal',json_encode($snapshot),$reference,$idempotency,$method]);
    $localId = (int)db()->lastInsertId();
    $payerEmail = (string)(current_user()['email'] ?? $input['email'] ?? '');
    $payload = ['transaction_amount'=>$amount,'description'=>'LigFlow - '.$plan['name'],'external_reference'=>$reference,'payer'=>['email'=>$payerEmail]];
    $notificationUrl = billing_public_webhook_url(env_value('APP_URL', ''));
    if ($notificationUrl !== null) $payload['notification_url'] = $notificationUrl;
    if ($method === 'pix') $payload['payment_method_id'] = 'pix';
    if ($method === 'boleto') {
        $payload['payment_method_id'] = 'bolbradesco';
        $payload['payer']['first_name'] = trim((string)($input['first_name'] ?? 'Cliente'));
        $payload['payer']['last_name'] = trim((string)($input['last_name'] ?? 'LigFlow'));
        $payload['payer']['identification'] = ['type'=>'CPF','number'=>preg_replace('/\D+/','',(string)($input['cpf'] ?? ''))];
    }
    if ($method === 'card') {
        $payload += ['token'=>(string)($input['token'] ?? ''),'installments'=>max(1,(int)($input['installments'] ?? 1)),'payment_method_id'=>(string)($input['payment_method_id'] ?? ''),'issuer_id'=>(string)($input['issuer_id'] ?? '')];
        if ($payload['token'] === '') throw new RuntimeException('Token seguro do cartao nao recebido.');
    }
    try {
        $remote = mercado_pago_request('POST', '/v1/payments', $payload, $idempotency);
        $status = billing_normalize_payment_status((string)($remote['status'] ?? ''), (string)($remote['status_detail'] ?? ''));
        $checkout = ['qr_code'=>(string)($remote['point_of_interaction']['transaction_data']['qr_code'] ?? ''),'qr_code_base64'=>(string)($remote['point_of_interaction']['transaction_data']['qr_code_base64'] ?? ''),'ticket_url'=>(string)($remote['transaction_details']['external_resource_url'] ?? '')];
        $expiresAtUtc = payment_datetime_to_utc($remote['date_of_expiration'] ?? null);
        db()->prepare('UPDATE payments SET provider_payment_id=?,status=?,provider_status=?,provider_status_detail=?,checkout_data_json=?,expires_at=?,updated_at=CURRENT_TIMESTAMP WHERE id=?')
            ->execute([(string)$remote['id'],$status,$remote['status'] ?? null,$remote['status_detail'] ?? null,json_encode($checkout),$expiresAtUtc,$localId]);
        if ($status === 'APPROVED') apply_approved_payment($localId, $remote);
        return one('SELECT * FROM payments WHERE id=?', [$localId]) ?: [];
    } catch (Throwable $e) {
        db()->prepare("UPDATE payments SET status='ERROR',provider_status_detail=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$e->getMessage(),$localId]);
        throw $e;
    }
}

function payment_datetime_to_utc(mixed $value): ?string
{
    $value = trim((string)$value);
    if ($value === '' || !preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', $value)) {
        return null;
    }
    try {
        return (new DateTimeImmutable($value, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d H:i:s');
    } catch (Throwable) {
        return null;
    }
}

function datetime_utc_display(?string $value, string $format = 'd/m/Y H:i:s', ?string $timezone = null): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}(?: \d{2}:\d{2}:\d{2})?$/', $value)) {
        return $value;
    }
    try {
        $targetTimezone = new DateTimeZone(trim((string)$timezone) ?: 'America/Sao_Paulo');
        // Datas sem horario sao datas de calendario do sistema, nao instantes UTC.
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
            ? new DateTimeImmutable($value, $targetTimezone)
            : new DateTimeImmutable($value, new DateTimeZone('UTC'));
        return $date->setTimezone($targetTimezone)
            ->format($format);
    } catch (Throwable) {
        if ($timezone !== null && $timezone !== 'America/Sao_Paulo') {
            return datetime_utc_display($value, $format, 'America/Sao_Paulo');
        }
        return $value;
    }
}

function date_br_display(?string $value, ?string $timezone = null): string
{
    if (!$value) return '-';
    return datetime_utc_display($value, 'd/m/Y H:i:s', $timezone);
}

function nvoip_error_hint(int $status, string $body): string
{
    $summary = trim(strip_tags($body));
    if (strlen($summary) > 180) {
        $summary = substr($summary, 0, 180) . '...';
    }
    if ($status === 403) {
        return '. Acesso negado: confira se a Napikey esta indo como parametro napikey, se o endpoint e da sua conta e se a conta/plano tem permissao para ligacoes. ' . ($summary ? 'Resposta: ' . $summary : '');
    }
    return $summary ? '. Resposta: ' . $summary : '';
}

function demo_recording_url(int $callId): ?string
{
    if ((int)nvoip_config()['recording_enabled'] !== 1) {
        return null;
    }
    return 'nvoip-demo://gravacao/' . $callId;
}

function normalize_phone(?string $phone): ?string
{
    $digits = preg_replace('/\D+/', '', (string)$phone);
    if ($digits === '') {
        return null;
    }
    if (strlen($digits) === 10 || strlen($digits) === 11) {
        $digits = '55' . $digits;
    }
    if (strlen($digits) < 12 || strlen($digits) > 13) {
        return null;
    }
    return '+' . $digits;
}

function is_phone_blocked(int $companyId, ?string $phone): bool
{
    $normalized = normalize_phone($phone);
    if (!$normalized) {
        return false;
    }
    return (bool)one('SELECT id FROM blocklist WHERE company_id = ? AND phone_e164 = ? LIMIT 1', [$companyId, $normalized]);
}

function phone_import_error_reason(string $rawPhone): string
{
    if (preg_match('/^\s*\d+(?:[,.]\d+)?E\+?\d+\s*$/i', $rawPhone)) {
        return 'Telefone em formato cientifico. Formate a coluna Telefone como texto na planilha e exporte o CSV novamente para preservar todos os digitos.';
    }
    return 'Telefone invalido';
}

function nvoip_phone_digits(?string $phone): string
{
    return preg_replace('/\D+/', '', (string)$phone) ?? '';
}

function is_test_phone_exception(?string $phone): bool
{
    return false;
}

function audit(string $action, string $resource = '', mixed $old = null, mixed $new = null): void
{
    $user = current_user();
    $companyId = $user['company_id'] ?? null;
    db()->prepare("INSERT INTO audit_logs (company_id, user_id, action, resource, ip_address, old_data, new_data)
        VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            $companyId,
            $user['id'] ?? null,
            $action,
            $resource,
            $_SERVER['REMOTE_ADDR'] ?? 'cli',
            $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
            $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
        ]);
}

function tenant_clause(string $alias = ''): array
{
    $user = current_user();
    if (is_platform_admin($user)) {
        return ['1=1', []];
    }
    $prefix = $alias ? $alias . '.' : '';
    return [$prefix . 'company_id = ?', [$user['company_id']]];
}

function rows(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function scalar(string $sql, array $params = []): mixed
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function first_payload_value(array $payload, array $keys): ?string
{
    foreach ($payload as $key => $value) {
        $normalized = strtolower((string)$key);
        if (in_array($normalized, $keys, true) && is_scalar($value) && trim((string)$value) !== '') {
            return trim((string)$value);
        }
        if (is_array($value)) {
            $found = first_payload_value($value, $keys);
            if ($found !== null) {
                return $found;
            }
        }
    }
    return null;
}

function recording_url_from_payload(array $payload): string
{
    $direct = first_payload_value($payload, [
        'recording_url',
        'recordingurl',
        'recording_link',
        'recordinglink',
        'recording',
        'record_url',
        'recordurl',
        'record_link',
        'recordlink',
        'audio_url',
        'audiourl',
        'media_url',
        'mediaurl',
        'download_url',
        'downloadurl',
        'file_url',
        'fileurl',
        'url_gravacao',
        'url_gravação',
        'gravacao_url',
        'gravação_url',
        'gravacao',
        'gravação',
        'link_gravacao',
        'link_gravação',
        'audio',
        'arquivo',
        'url',
    ]);
    if ($direct && preg_match('~^https?://~i', $direct)) {
        return $direct;
    }
    return '';
}

function webhook_match_key(array $payload): string
{
    return first_payload_value($payload, ['external_call_id', 'call_id', 'callid', 'id', 'uuid', 'uniqueid', 'linkedid'])
        ?: (first_payload_value($payload, ['destination', 'destination_number', 'destinationnumber', 'called', 'called_number', 'callednumber', 'to', 'phone', 'number', 'dst', 'destino', 'numero', 'numero_destino', 'numerodestino']) ?: '');
}

function log_nvoip_webhook(?int $companyId, ?int $callId, array $payload, string $status, string $recordingUrl, string $matchKey): void
{
    db()->prepare("INSERT INTO nvoip_webhook_logs (company_id, call_id, status, recording_url, match_key, payload)
        VALUES (?, ?, ?, NULLIF(?, ''), ?, ?)")
        ->execute([$companyId, $callId, $status, $recordingUrl, $matchKey, json_encode($payload, JSON_UNESCAPED_UNICODE)]);
}

function log_call_status(?int $companyId, ?int $callId, string $provider, string $status, string $message, array $payload = []): void
{
    db()->prepare("INSERT INTO call_status_logs (company_id, call_id, provider, status, message, payload)
        VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$companyId, $callId, $provider, $status, $message, json_encode($payload, JSON_UNESCAPED_UNICODE)]);
}

function backfill_recordings_from_call_events(int $companyId): int
{
    $events = rows("
        SELECT ce.call_id, ce.payload
        FROM call_events ce
        JOIN calls co ON co.id = ce.call_id
        WHERE ce.company_id = ?
          AND ce.call_id IS NOT NULL
          AND ce.event_name IN ('nvoip.webhook','sip.ended','sip.failed')
          AND (co.recording_url IS NULL OR co.recording_url = '' OR co.recording_url LIKE 'nvoip-demo:%')
        ORDER BY ce.id DESC
        LIMIT 300
    ", [$companyId]);
    $updated = 0;
    foreach ($events as $event) {
        $payload = json_decode((string)$event['payload'], true);
        if (!is_array($payload)) {
            continue;
        }
        $recording = recording_url_from_payload($payload);
        if ($recording === '') {
            continue;
        }
        db()->prepare("UPDATE calls SET recording_url = ?, updated_at = datetime('now') WHERE id = ? AND company_id = ?")
            ->execute([$recording, $event['call_id'], $companyId]);
        $updated++;
    }
    return $updated;
}

function webhook_company_id(array $payload): ?int
{
    $secret = (string)($_SERVER['HTTP_X_NVOIP_SECRET'] ?? $_GET['secret'] ?? '');
    if ($secret !== '') {
        $row = one("SELECT company_id FROM integration_settings WHERE provider = 'nvoip' AND webhook_secret = ? ORDER BY id DESC LIMIT 1", [$secret]);
        if ($row) {
            return (int)$row['company_id'];
        }
    }
    $companyId = (int)($payload['company_id'] ?? $payload['companyId'] ?? 0);
    return $companyId > 0 ? $companyId : null;
}

function find_call_from_webhook(array $payload, ?int $companyId = null): ?array
{
    $externalId = first_payload_value($payload, ['external_call_id', 'provider_call_id', 'call_id', 'callid', 'id', 'uuid', 'uniqueid', 'linkedid']);
    if ($externalId) {
        $params = [$externalId];
        $sql = 'SELECT * FROM calls WHERE (external_call_id = ? OR provider_call_id = ?)';
        $params[] = $externalId;
        if ($companyId) {
            $sql .= ' AND company_id = ?';
            $params[] = $companyId;
        }
        $call = one($sql . ' ORDER BY id DESC LIMIT 1', $params);
        if ($call) {
            return $call;
        }
    }

    $rawPhone = first_payload_value($payload, ['destination', 'destination_number', 'destinationnumber', 'called', 'called_number', 'callednumber', 'to', 'phone', 'number', 'dst', 'destino', 'numero', 'numero_destino', 'numerodestino']);
    $phone = normalize_phone($rawPhone);
    if (!$phone) {
        return null;
    }
    $params = [$phone];
    $sql = "SELECT * FROM calls WHERE destination_number = ? AND created_at >= datetime('now', '-2 days')";
    if ($companyId) {
        $sql .= ' AND company_id = ?';
        $params[] = $companyId;
    }
    return one($sql . " ORDER BY CASE WHEN status IN ('ringing','answered','pos_atendimento','in_progress','calling_origin') THEN 0 ELSE 1 END, id DESC LIMIT 1", $params);
}

function post(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('?page=login');
    }
}

function flash(?string $message = null, string $type = 'ok'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function handle_post(): void
{
    $action = post('action');
    $pdo = db();

    if ($action === 'login') {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND deleted_at IS NULL');
        $stmt->execute([post('email')]);
        $user = $stmt->fetch();
        if ($user && password_verify((string)post('password'), $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            audit('login', 'users:' . $user['id']);
            redirect('?page=dashboard');
        }
        flash('E-mail ou senha invalidos.', 'error');
        redirect('?page=login');
    }

    require_login();
    $user = current_user();
    $companyId = (is_platform_admin($user) && post('company_id')) ? (int)post('company_id') : (int)$user['company_id'];
    if ($action === 'accept_terms') {
        if (!post('terms_acceptance')) {
            flash('Confirme a leitura e o aceite dos Termos de Uso e da Politica de Privacidade.', 'error');
            redirect('?page=dashboard');
        }
        $pdo->prepare("UPDATE users SET terms_accepted_version=?, terms_accepted_at=datetime('now') WHERE id=?")
            ->execute([TERMS_VERSION, (int)$user['id']]);
        audit('aceitou_termos_de_uso', 'users:' . (int)$user['id'], null, ['version' => TERMS_VERSION]);
        flash('Termos aceitos com sucesso.');
        redirect('?page=dashboard');
    }
    if (!user_has_accepted_current_terms($user)) {
        flash('Aceite os Termos de Uso e a Politica de Privacidade para continuar.', 'error');
        redirect('?page=dashboard');
    }
    if (!is_platform_admin($user) && tenant_billing_state((int)$user['company_id'])['blocked'] && !in_array($action, ['create_payment'], true)) {
        flash('Plano bloqueado. Regularize o pagamento para continuar.', 'error');
        redirect('?page=costs');
    }

    if ($action === 'save_mercado_pago_settings') {
        if (!is_platform_admin($user)) { http_response_code(403); exit('Acesso negado.'); }
        if (!function_exists('openssl_encrypt')) { flash('OpenSSL e obrigatorio para salvar credenciais privadas.', 'error'); redirect('?page=settings#mercado-pago'); }
        $existing = one('SELECT * FROM payment_settings WHERE id=1') ?: [];
        $token = trim((string)post('access_token'));
        $secret = trim((string)post('webhook_secret'));
        if ($token === '') $token = decrypt_secret((string)($existing['access_token_encrypted'] ?? ''));
        if ($secret === '') $secret = decrypt_secret((string)($existing['webhook_secret_encrypted'] ?? ''));
        $pdo->prepare("INSERT INTO payment_settings (id,active,environment,public_key,access_token_encrypted,webhook_secret_encrypted,pix_enabled,card_enabled,boleto_enabled,updated_by,updated_at) VALUES (1,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(id) DO UPDATE SET active=excluded.active,environment=excluded.environment,public_key=excluded.public_key,access_token_encrypted=excluded.access_token_encrypted,webhook_secret_encrypted=excluded.webhook_secret_encrypted,pix_enabled=excluded.pix_enabled,card_enabled=excluded.card_enabled,boleto_enabled=excluded.boleto_enabled,updated_by=excluded.updated_by,updated_at=CURRENT_TIMESTAMP")
            ->execute([post('active')?1:0,post('environment')==='production'?'production':'test',trim((string)post('public_key')),encrypt_secret($token),encrypt_secret($secret),post('pix_enabled')?1:0,post('card_enabled')?1:0,post('boleto_enabled')?1:0,$user['id']]);
        audit('configurou_mercado_pago', 'payment_settings:1');
        flash('Mercado Pago atualizado.');
        redirect('?page=settings#mercado-pago');
    }

    if ($action === 'test_mercado_pago') {
        if (!is_platform_admin($user)) { http_response_code(403); exit('Acesso negado.'); }
        try { mercado_pago_request('GET', '/users/me'); flash('Conexao com Mercado Pago realizada com sucesso.'); }
        catch (Throwable $e) { flash('Falha no Mercado Pago: '.$e->getMessage(), 'error'); }
        redirect('?page=settings#mercado-pago');
    }

    if ($action === 'save_google_places_settings') {
        if (!is_platform_admin($user)) { http_response_code(403); exit('Acesso negado.'); }
        if (!function_exists('openssl_encrypt')) { flash('OpenSSL e obrigatorio para salvar a chave privada.', 'error'); redirect('?page=settings#google-places'); }
        $existing = one('SELECT * FROM google_places_settings WHERE id = 1') ?: [];
        $apiKey = trim((string)post('google_places_api_key'));
        if ($apiKey === '') $apiKey = decrypt_secret((string)($existing['api_key_encrypted'] ?? ''));
        $pdo->prepare("INSERT INTO google_places_settings (id,active,api_key_encrypted,updated_by,updated_at) VALUES (1,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(id) DO UPDATE SET active=excluded.active,api_key_encrypted=excluded.api_key_encrypted,updated_by=excluded.updated_by,updated_at=CURRENT_TIMESTAMP")
            ->execute([post('google_places_active') ? 1 : 0, encrypt_secret($apiKey), (int)$user['id']]);
        audit('configurou_google_places', 'google_places_settings:1', null, ['active' => post('google_places_active') ? 1 : 0, 'has_api_key' => $apiKey !== '']);
        flash('Google Places atualizado.');
        redirect('?page=settings#google-places');
    }

    if ($action === 'test_google_places') {
        if (!is_platform_admin($user)) { http_response_code(403); exit('Acesso negado.'); }
        try {
            google_places_search(['segment' => 'restaurante', 'city' => 'Sao Paulo', 'state' => 'SP']);
            flash('Conexao com Google Places realizada com sucesso.');
        } catch (Throwable $e) {
            flash('Falha no Google Places: ' . $e->getMessage(), 'error');
        }
        redirect('?page=settings#google-places');
    }

    if ($action === 'search_radar_leads' && can('radar')) {
        try {
            $filters = [
                'segment' => trim((string)post('segment')),
                'state' => trim((string)post('state')),
                'city' => trim((string)post('city')),
                'neighborhood' => trim((string)post('neighborhood')),
                'street' => trim((string)post('street')),
                'only_with_phone' => post('only_with_phone') ? 1 : 0,
            ];
            $result = google_places_search_pages($filters);
            $registered = radar_filter_available_places((int)$user['company_id'], $result['places']);
            $_SESSION['radar_leads'] = [
                'company_id' => (int)$user['company_id'],
                'filters' => $filters,
                'places' => $registered['places'],
                'next_page_token' => $result['next_page_token'],
                'pages_fetched' => $result['pages_fetched'],
                'discarded' => $registered['discarded'],
                'target_count' => max(1, min(1000, (int)post('target_count', '20'))),
                'list_id' => 0,
                'added' => 0,
            ];
            $message = count($registered['places']) . ' empresa(s) nova(s) encontrada(s) em ' . $result['pages_fetched'] . ' pagina(s). ' . $registered['discarded'] . ' repetida(s) foram descartadas.';
            if ($result['page_error'] !== '') $message .= ' A ultima pagina nao foi carregada: ' . $result['page_error'];
            flash($message);
        } catch (Throwable $e) {
            flash('Nao foi possivel buscar empresas: ' . $e->getMessage(), 'error');
        }
        redirect('?page=radar');
    }

    if ($action === 'search_radar_more' && can('radar')) {
        try {
            $stored = $_SESSION['radar_leads'] ?? [];
            if ((int)($stored['company_id'] ?? 0) !== (int)$user['company_id'] || empty($stored['next_page_token'])) {
                throw new RuntimeException('Nao ha mais resultados para esta busca.');
            }
            $remainingPages = 3 - max(0, (int)($stored['pages_fetched'] ?? 1));
            if ($remainingPages < 1) throw new RuntimeException('O limite de 60 resultados desta busca ja foi atingido.');
            $result = google_places_search_pages((array)$stored['filters'], (string)$stored['next_page_token'], $remainingPages);
            $registered = radar_filter_available_places((int)$user['company_id'], $result['places'], (array)($stored['places'] ?? []));
            $stored['places'] = array_merge((array)($stored['places'] ?? []), $registered['places']);
            $stored['next_page_token'] = $result['next_page_token'];
            $stored['pages_fetched'] = max(0, (int)($stored['pages_fetched'] ?? 1)) + (int)$result['pages_fetched'];
            $stored['discarded'] = (int)($stored['discarded'] ?? 0) + $registered['discarded'];
            $_SESSION['radar_leads'] = $stored;
            $message = count($registered['places']) . ' empresa(s) nova(s) adicionada(s) ao resultado.';
            if ($result['page_error'] !== '') $message .= ' A pagina seguinte nao foi carregada: ' . $result['page_error'];
            flash($message);
        } catch (Throwable $e) {
            flash('Nao foi possivel buscar mais empresas: ' . $e->getMessage(), 'error');
        }
        redirect('?page=radar');
    }

    if ($action === 'create_radar_list' && can('radar')) {
        try {
            $places = radar_session_places((array)post('place_ids', []));
            if (!$places) throw new RuntimeException('Selecione ao menos uma empresa nova.');
            $name = trim((string)post('list_name'));
            if ($name === '') throw new RuntimeException('Informe o nome da lista.');
            $target = max(1, min(1000, (int)post('target_count', '20')));
            $pdo->prepare("INSERT INTO contact_lists (company_id,name,description,source,status,tags,radar_target_leads,created_by) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([(int)$user['company_id'], $name, 'Lista criada pelo Radar de Leads.', 'Google Places', 'Disponivel', 'radar', $target, (int)$user['id']]);
            $listId = (int)$pdo->lastInsertId();
            $added = radar_add_places_to_list((int)$user['company_id'], (int)$user['id'], $listId, $places);
            unset($_SESSION['radar_leads']);
            audit('criou_lista_radar', 'contact_lists:' . $listId, null, ['added' => $added]);
            flash('Lista criada com ' . $added . ' lead(s) do Radar.');
        } catch (Throwable $e) {
            flash('Nao foi possivel criar a lista: ' . $e->getMessage(), 'error');
        }
        redirect('?page=radar');
    }

    if ($action === 'add_radar_to_list' && can('radar')) {
        try {
            $places = radar_session_places((array)post('place_ids', []));
            $listId = (int)post('list_id');
            if (!$places || !$listId) throw new RuntimeException('Selecione empresas e uma lista de destino.');
            $added = radar_add_places_to_list((int)$user['company_id'], (int)$user['id'], $listId, $places);
            $target = max(1, min(1000, (int)post('target_count', '20')));
            $pdo->prepare('UPDATE contact_lists SET radar_target_leads = CASE WHEN COALESCE(radar_target_leads, 0) <= 0 THEN ? ELSE radar_target_leads END WHERE id = ? AND company_id = ?')
                ->execute([$target, $listId, (int)$user['company_id']]);
            unset($_SESSION['radar_leads']);
            audit('adicionou_leads_radar', 'contact_lists:' . $listId, null, ['added' => $added]);
            flash($added . ' lead(s) adicionada(s) a lista selecionada.');
        } catch (Throwable $e) {
            flash('Nao foi possivel adicionar os leads: ' . $e->getMessage(), 'error');
        }
        redirect('?page=radar');
    }

    if ($action === 'create_radar_campaign' && can('campaigns')) {
        try {
            $listId = (int)post('list_id');
            $list = one('SELECT * FROM contact_lists WHERE id = ? AND company_id = ?', [$listId, (int)$user['company_id']]);
            if (!$list) throw new RuntimeException('Lista nao encontrada.');
            $name = trim((string)post('campaign_name')) ?: 'Radar - ' . (string)$list['name'];
            $pdo->prepare("INSERT INTO campaigns (company_id,list_id,name,description,dialer_type,sip_trunk,script,max_attempts,status) VALUES (?,?,?,?,?,'Telefonia gerenciada',?,1,'Ativa')")
                ->execute([(int)$user['company_id'], $listId, $name, 'Campanha criada a partir do Radar de Leads.', 'progressivo', 'Confirme o interesse do contato e registre o resultado.']);
            audit('criou_campanha_radar', 'campaigns:' . $pdo->lastInsertId(), null, ['list_id' => $listId]);
            flash('Campanha criada a partir da lista do Radar.');
        } catch (Throwable $e) {
            flash('Nao foi possivel criar a campanha: ' . $e->getMessage(), 'error');
        }
        redirect('?page=radar');
    }

    if ($action === 'create_payment' && can('costs')) {
        try {
            $payment = create_tenant_payment((int)$user['company_id'], (int)$user['id'], strtolower((string)post('payment_method')), $_POST);
            flash('Pagamento criado. Acompanhe o status abaixo.');
            redirect('?page=costs&payment_id='.(int)$payment['id']);
        } catch (Throwable $e) {
            flash('Nao foi possivel criar o pagamento: '.$e->getMessage(), 'error');
            redirect('?page=costs');
        }
    }

    if ($action === 'save_company' && can('companies')) {
        $pdo->prepare("INSERT INTO companies (legal_name, trade_name, cnpj, email, phone, plan, max_users, max_agents, max_channels, monthly_minutes_limit, status, call_window, voip_provider)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([post('legal_name'), post('trade_name'), post('cnpj'), post('email'), post('phone'), post('plan'), post('max_users'), post('max_agents'), post('max_channels'), post('monthly_minutes_limit'), post('status'), post('call_window'), post('voip_provider')]);
        audit('criou_empresa', 'companies:' . $pdo->lastInsertId(), null, $_POST);
        flash('Empresa cadastrada.');
        redirect('?page=companies');
    }

    if ($action === 'create_client_account' && can('companies')) {
        create_client_account($companyId, (int)$user['id']);
        redirect('?page=companies');
    }

    if ($action === 'update_client_account' && can('companies')) {
        update_client_account((int)post('client_id'));
        redirect('?page=companies');
    }

    if ($action === 'remove_client' && can('companies')) {
        remove_or_hide_client((int)post('client_id'));
        redirect('?page=companies');
    }

    if ($action === 'save_plan' && can('plans')) {
        save_plan();
        redirect('?page=plans');
    }

    if ($action === 'update_plan' && can('plans')) {
        update_plan_record((int)post('plan_id'));
        redirect('?page=plans');
    }

    if ($action === 'delete_plan' && can('plans')) {
        delete_plan_record((int)post('plan_id'));
        redirect('?page=plans');
    }

    if ($action === 'save_access_profile' && can('users')) {
        if (!is_platform_admin($user)) {
            flash('Somente administrador pode criar perfis de acesso.', 'error');
            redirect('?page=users');
        }
        $profileId = save_access_profile((int)post('profile_id'), $companyId, (int)$user['id']);
        redirect('?page=users' . ($profileId > 0 ? '&profile_id=' . $profileId : ''));
    }

    if ($action === 'delete_access_profile' && can('users')) {
        if (!is_platform_admin($user)) {
            flash('Somente administrador pode excluir perfis de acesso.', 'error');
            redirect('?page=users');
        }
        delete_access_profile((int)post('profile_id'), $companyId);
        redirect('?page=users');
    }

    if ($action === 'save_user' && can('users')) {
        if (!is_platform_admin($user)) {
            flash('Somente administrador pode criar novos acessos.', 'error');
            redirect('?page=users');
        }
        $email = trim((string)post('email'));
        if ($email === '' || one('SELECT id FROM users WHERE email = ?', [$email])) {
            flash('Ja existe um acesso cadastrado com este e-mail. Use outro e-mail ou edite o acesso existente.', 'error');
            redirect('?page=users');
        }
        try {
            $pdo->exec('BEGIN IMMEDIATE');
            $accessProfileId = (int)post('access_profile_id');
            $profileRole = '';
            if ($accessProfileId > 0 && !one('SELECT id FROM access_profiles WHERE id = ? AND company_id = ?', [$accessProfileId, $companyId])) {
                $accessProfileId = 0;
            }
            if ($accessProfileId > 0) {
                $profileRole = (string)(one('SELECT role_key FROM access_profiles WHERE id = ? AND company_id = ?', [$accessProfileId, $companyId])['role_key'] ?? '');
            }
            $role = $profileRole ?: 'usuario_operacional';
            $pdo->prepare("INSERT INTO users (company_id, team_id, access_profile_id, name, email, password_hash, role, allowed_modules_json, phone, extension, status, work_hours, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$companyId, post('team_id') ?: null, $accessProfileId ?: null, post('name'), $email, password_hash((string)post('password', 'admin123'), PASSWORD_DEFAULT), $role, selected_modules_json(post('modules', [])), post('phone'), post('extension'), post('status'), post('work_hours'), $user['id']]);
            $newUserId = (int)$pdo->lastInsertId();
            if (asterisk_new_users_use_provisioning()) {
                asterisk_reserve_user_extension($pdo, $companyId, $newUserId, (string)post('asterisk_extension'), (string)post('status'));
            } else {
                sync_user_asterisk_extension($companyId, $newUserId, (string)post('asterisk_extension'), (string)post('status'));
            }
            $pdo->exec('COMMIT');
            audit('criou_usuario', 'users:' . $newUserId, null, $_POST);
            flash('Usuario cadastrado. Senha inicial: ' . h((string)post('password', 'admin123')));
        } catch (Throwable $e) {
            try { $pdo->exec('ROLLBACK'); } catch (Throwable) { }
            flash($e instanceof InvalidArgumentException ? $e->getMessage() : 'Nao foi possivel cadastrar o acesso. Confira se o e-mail ainda nao esta em uso.', 'error');
        }
        redirect('?page=users');
    }

    if ($action === 'update_user_access' && can('users')) {
        update_user_access((int)post('user_id'), $companyId);
        redirect('?page=users');
    }

    if ($action === 'delete_user_access' && can('users')) {
        delete_user_access((int)post('user_id'), $companyId);
        redirect('?page=users');
    }

    if ($action === 'update_my_account' && can('account')) {
        update_my_account((int)$user['id']);
        redirect('?page=account');
    }

    if ($action === 'request_my_password_reset' && can('account')) {
        request_my_password_reset((int)$user['id']);
        redirect('?page=account');
    }

    if ($action === 'save_team' && can('teams')) {
        $pdo->prepare("INSERT INTO teams (company_id, name, description, supervisor_id, daily_goal, simultaneous_limit, priority, voip_queue)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$companyId, post('name'), post('description'), post('supervisor_id') ?: null, post('daily_goal'), post('simultaneous_limit'), post('priority'), post('voip_queue')]);
        audit('criou_equipe', 'teams:' . $pdo->lastInsertId(), null, $_POST);
        flash('Equipe cadastrada.');
        redirect('?page=teams');
    }

    if ($action === 'create_list' && can('lists')) {
        $pdo->prepare("INSERT INTO contact_lists (company_id, name, description, source, tags, created_by) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$companyId, post('name'), post('description'), post('source', 'CSV'), post('tags'), $user['id']]);
        audit('criou_lista', 'contact_lists:' . $pdo->lastInsertId(), null, $_POST);
        flash('Lista criada. Agora importe os leads por CSV.');
        redirect('?page=lists');
    }

    if ($action === 'create_list_import_csv' && can('lists')) {
        $token = prepare_new_list_csv_import($companyId, (int)$user['id']);
        redirect('?page=lists' . ($token ? '&import_token=' . urlencode($token) : ''));
    }

    if ($action === 'import_csv' && can('lists')) {
        flash('Para proteger listas ja trabalhadas, crie uma nova lista junto com o CSV.', 'error');
        redirect('?page=lists');
    }

    if ($action === 'confirm_csv_import' && can('lists')) {
        $listId = (int)post('list_id');
        $importToken = (string)post('import_token');
        $importedListId = confirm_csv_import($importToken, $listId, $companyId, (int)$user['id']);
        redirect('?page=lists');
    }

    if ($action === 'create_remessa_from_selection' && can('lists')) {
        $sourceListId = (int)post('source_list_id');
        $statusFilters = is_array($_POST['lead_status_filters'] ?? null) ? $_POST['lead_status_filters'] : [];
        $selectedIds = is_array($_POST['selected_contacts'] ?? null) ? $_POST['selected_contacts'] : [];
        $newListId = create_remessa_from_selected_contacts($sourceListId, $selectedIds, $companyId, (int)$user['id'], $statusFilters, (string)post('remessa_name'));
        redirect('?page=lists' . ($newListId > 0 ? '&list_id=' . $newListId : ($sourceListId > 0 ? '&list_id=' . $sourceListId : '')));
    }

    if ($action === 'update_contact' && can('lists')) {
        update_contact((int)post('contact_id'), $companyId);
        redirect('?page=lists&list_id=' . (int)post('list_id'));
    }

    if ($action === 'delete_contact' && can('lists')) {
        delete_contact((int)post('contact_id'), $companyId);
        redirect('?page=lists&list_id=' . (int)post('list_id'));
    }

    if ($action === 'reset_list' && can('lists')) {
        if (trim((string)post('reset_confirmation')) !== 'RESETAR') {
            flash('Digite RESETAR para confirmar o reset da lista.', 'error');
            redirect('?page=lists');
        }
        reset_contact_list((int)post('list_id'), $companyId);
        redirect('?page=lists');
    }

    if ($action === 'delete_list' && can('lists')) {
        delete_contact_list((int)post('list_id'), $companyId);
        redirect('?page=lists');
    }

    if ($action === 'create_campaign' && can('campaigns')) {
        $campaignStartsAt = local_datetime_to_utc_storage((string)post('starts_at'));
        $campaignEndsAt = local_datetime_to_utc_storage((string)post('ends_at'));
        $pdo->prepare("INSERT INTO campaigns (company_id, list_id, team_id, supervisor_id, name, description, dialer_type, caller_id, sip_trunk, script, starts_at, ends_at, call_window, max_attempts, simultaneous_calls, retry_interval_minutes, priority, recording_enabled, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$companyId, post('list_id'), post('team_id') ?: null, post('supervisor_id') ?: null, post('name'), post('description'), post('dialer_type'), post('caller_id'), post('sip_trunk'), post('script'), $campaignStartsAt, $campaignEndsAt, post('call_window'), max(1, (int)post('max_attempts', 1)), campaign_parallelism_input(post('simultaneous_calls', 1)), post('retry_interval_minutes'), post('priority'), post('recording_enabled') ? 1 : 0, post('status')]);
        audit('criou_campanha', 'campaigns:' . $pdo->lastInsertId(), null, $_POST);
        flash('Campanha criada.');
        redirect('?page=campaigns');
    }

    if ($action === 'update_campaign' && can('campaigns')) {
        $campaignId = (int)post('campaign_id');
        [$where, $whereParams] = tenant_clause();
        $campaign = one("SELECT * FROM campaigns WHERE id = ? AND {$where} AND status <> 'Manual'", array_merge([$campaignId], $whereParams));
        if (!$campaign) {
            flash('Campanha nao encontrada para edicao.', 'error');
            redirect('?page=campaigns');
        }

        $campaignStartsAt = local_datetime_to_utc_storage((string)post('starts_at'));
        $campaignEndsAt = local_datetime_to_utc_storage((string)post('ends_at'));
        $pdo->prepare("UPDATE campaigns
            SET list_id = ?, team_id = ?, supervisor_id = ?, name = ?, description = ?, dialer_type = ?, caller_id = ?, sip_trunk = ?, script = ?, starts_at = ?, ends_at = ?, call_window = ?, max_attempts = ?, simultaneous_calls = ?, retry_interval_minutes = ?, priority = ?, recording_enabled = ?, status = ?
            WHERE id = ?")
            ->execute([post('list_id'), post('team_id') ?: null, post('supervisor_id') ?: null, post('name'), post('description'), post('dialer_type'), post('caller_id'), post('sip_trunk'), post('script'), $campaignStartsAt, $campaignEndsAt, post('call_window'), max(1, (int)post('max_attempts', 1)), campaign_parallelism_input(post('simultaneous_calls', 1)), post('retry_interval_minutes'), post('priority'), post('recording_enabled') ? 1 : 0, post('status'), $campaignId]);
        audit('editou_campanha', 'campaigns:' . $campaignId, $campaign, $_POST);
        flash('Campanha atualizada.');
        redirect('?page=campaigns');
    }

    if ($action === 'delete_campaign' && can('campaigns')) {
        $campaignId = (int)post('campaign_id');
        [$where, $whereParams] = tenant_clause();
        $campaign = one("SELECT * FROM campaigns WHERE id = ? AND {$where} AND status <> 'Manual'", array_merge([$campaignId], $whereParams));
        if (!$campaign) {
            flash('Campanha nao encontrada para exclusao.', 'error');
            redirect('?page=campaigns');
        }
        $linkedCalls = (int)(one('SELECT COUNT(*) total FROM calls WHERE campaign_id = ?', [$campaignId])['total'] ?? 0);
        if ($linkedCalls > 0) {
            flash('Esta campanha ja possui chamadas vinculadas e nao pode ser excluida. Finalize ou pause a campanha para manter o historico.', 'error');
            redirect('?page=campaigns');
        }
        $pdo->prepare('DELETE FROM campaigns WHERE id = ?')->execute([$campaignId]);
        audit('excluiu_campanha', 'campaigns:' . $campaignId, $campaign, null);
        flash('Campanha excluida.');
        redirect('?page=campaigns');
    }

    if ($action === 'save_global_telephony_mode' && is_platform_admin($user)) {
        $mode = strtoupper((string)post('active_mode', 'NVOIP_DIRECT'));
        if (!in_array($mode, ['NVOIP_DIRECT', 'ASTERISK'], true)) {
            flash('Provedor de telefonia invalido.', 'error');
            redirect('?page=settings#integracoes-cadastradas');
        }
        $asterisk = asterisk_config();
        if ($mode === 'ASTERISK' && empty($asterisk['enabled'])) {
            flash('Habilite e configure o Asterisk antes de torna-lo o provedor ativo.', 'error');
            redirect('?page=settings#asterisk');
        }
        db()->prepare("INSERT INTO asterisk_settings (id, active_mode, updated_by, updated_at) VALUES (1, ?, ?, datetime('now')) ON CONFLICT(id) DO UPDATE SET active_mode = excluded.active_mode, updated_by = excluded.updated_by, updated_at = excluded.updated_at")
            ->execute([$mode, (int)$user['id']]);
        audit('atualizou_provedor_telefonia', 'asterisk_settings:1', null, ['active_mode' => $mode]);
        flash('Provedor ativo para novas chamadas: ' . ($mode === 'ASTERISK' ? 'Asterisk' : 'Nvoip nativa') . '.');
        redirect('?page=settings#integracoes-cadastradas');
    }
    if (($action === 'save_asterisk_settings' || $action === 'test_asterisk_connection') && is_platform_admin($user)) {
        $existing = one('SELECT * FROM asterisk_settings WHERE id = 1') ?: [];
        if ($action === 'test_asterisk_connection') {
            try {
                $health = (new AsteriskProvider(asterisk_config()))->health();
                flash(!empty($health['ari']) ? 'Conexao ARI validada.' : 'ARI nao respondeu. Confira URL, usuario e senha.', !empty($health['ari']) ? 'ok' : 'error');
            } catch (Throwable $e) {
                flash('Falha ao testar Asterisk: ' . $e->getMessage(), 'error');
            }
            redirect('?page=settings#asterisk');
        }
        $mode = strtoupper((string)post('active_mode', 'NVOIP_DIRECT'));
        $route = strtoupper((string)post('active_route', 'NVOIP_TRUNK'));
        $environment = strtolower((string)post('environment', 'test'));
        $ariUrl = trim((string)post('ari_url'));
        $ariWsUrl = trim((string)post('ari_ws_url'));
        $sipWssUrl = trim((string)post('sip_wss_url'));
        $sipDomain = trim((string)post('sip_domain'));
        $consultantEndpoint = trim((string)post('consultant_endpoint'));
        $webrtcContext = trim((string)post('webrtc_context'));
        $nvoipTrunk = trim((string)post('nvoip_trunk', 'nvoip'));
        if ($nvoipTrunk === '' || strtoupper($nvoipTrunk) === 'NVOIP_TRUNK') $nvoipTrunk = 'nvoip';
        $directcallTrunk = trim((string)post('directcall_trunk'));
        if (!in_array($mode, ['NVOIP_DIRECT', 'ASTERISK'], true) || !in_array($route, ['NVOIP_TRUNK', 'DIRECTCALL_TRUNK'], true) || !in_array($environment, ['test', 'production'], true)) {
            flash('Modo, rota ou ambiente Asterisk invalido.', 'error'); redirect('?page=settings#asterisk');
        }
        if (!valid_asterisk_trunk_identifier($nvoipTrunk) || $directcallTrunk === '' || !valid_asterisk_trunk_identifier($directcallTrunk) || ($webrtcContext !== '' && !valid_asterisk_trunk_identifier($webrtcContext))) {
            flash('Contexto WebRTC ou tronco Asterisk invalido. Use apenas letras, numeros, hifen e underscore.', 'error'); redirect('?page=settings#asterisk');
        }
        if (($sipWssUrl !== '' && !valid_asterisk_webrtc_wss_url($sipWssUrl)) || ($sipDomain !== '' && !valid_asterisk_webrtc_domain($sipDomain)) || ($consultantEndpoint !== '' && !valid_asterisk_webrtc_endpoint($consultantEndpoint))) {
            flash('Configuracao SIP/WebRTC invalida. Informe WSS (ou WS apenas em localhost), dominio sem protocolo e endpoint PJSIP valido.', 'error'); redirect('?page=settings#asterisk');
        }
        if ((int)post('enabled') === 1 && (!filter_var($ariUrl, FILTER_VALIDATE_URL) || !filter_var($ariWsUrl, FILTER_VALIDATE_URL) || !valid_asterisk_webrtc_wss_url($sipWssUrl) || !valid_asterisk_webrtc_domain($sipDomain) || !valid_asterisk_webrtc_endpoint($consultantEndpoint) || $webrtcContext === '')) {
            flash('Informe ARI, WebSocket, WSS SIP, dominio, endpoint e contexto WebRTC validos para habilitar o Asterisk.', 'error'); redirect('?page=settings#asterisk');
        }
        $ariPassword = trim((string)post('ari_password'));
        $ariPasswordEncrypted = $ariPassword !== '' ? encrypt_secret($ariPassword) : (string)($existing['ari_password_encrypted'] ?? '');
        $webrtcPassword = trim((string)post('webrtc_password'));
        $webrtcPasswordEncrypted = $webrtcPassword !== '' ? encrypt_secret($webrtcPassword) : (string)($existing['webrtc_password_encrypted'] ?? '');
        $webrtcPasswordChange = $webrtcPassword !== '' ? (!empty($existing['webrtc_password_encrypted']) ? 'substituida' : 'criada') : 'preservada';
        $extensionStart = (int)post('extension_start', 1000);
        $extensionEnd = (int)post('extension_end', 9999);
        $agentUrl = trim((string)post('provisioning_agent_url'));
        $agentSecret = trim((string)post('provisioning_agent_secret'));
        $agentSecretEncrypted = $agentSecret !== '' ? encrypt_secret($agentSecret) : (string)($existing['provisioning_agent_secret_encrypted'] ?? '');
        $agentTimeout = max(3, min(60, (int)post('provisioning_agent_timeout_seconds', 10)));
        if ($extensionStart < 1 || $extensionEnd < $extensionStart || $extensionEnd > 999999999999999999) {
            flash('Faixa de ramais Asterisk invalida.', 'error'); redirect('?page=settings#asterisk');
        }
        if ($agentUrl !== '' && !valid_asterisk_provisioning_agent_url($agentUrl)) {
            flash('URL do agente de provisionamento invalida.', 'error'); redirect('?page=settings#asterisk');
        }
        db()->prepare("INSERT INTO asterisk_settings (id, enabled, environment, active_mode, active_route, ari_url, ari_ws_url, ari_username, ari_password_encrypted, stasis_app, originate_timeout_seconds, bridge_timeout_seconds, reconnect_initial_seconds, reconnect_max_seconds, sip_wss_url, sip_domain, consultant_endpoint, webrtc_password_encrypted, webrtc_context, nvoip_trunk, directcall_trunk, nvoip_trunk_config_json, directcall_trunk_config_json, extension_start, extension_end, provisioning_agent_url, provisioning_agent_secret_encrypted, provisioning_agent_timeout_seconds, updated_by, updated_at)
            VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
            ON CONFLICT(id) DO UPDATE SET enabled=excluded.enabled, environment=excluded.environment, active_mode=excluded.active_mode, active_route=excluded.active_route, ari_url=excluded.ari_url, ari_ws_url=excluded.ari_ws_url, ari_username=excluded.ari_username, ari_password_encrypted=excluded.ari_password_encrypted, stasis_app=excluded.stasis_app, originate_timeout_seconds=excluded.originate_timeout_seconds, bridge_timeout_seconds=excluded.bridge_timeout_seconds, reconnect_initial_seconds=excluded.reconnect_initial_seconds, reconnect_max_seconds=excluded.reconnect_max_seconds, sip_wss_url=excluded.sip_wss_url, sip_domain=excluded.sip_domain, consultant_endpoint=excluded.consultant_endpoint, webrtc_password_encrypted=excluded.webrtc_password_encrypted, webrtc_context=excluded.webrtc_context, nvoip_trunk=excluded.nvoip_trunk, directcall_trunk=excluded.directcall_trunk, nvoip_trunk_config_json=excluded.nvoip_trunk_config_json, directcall_trunk_config_json=excluded.directcall_trunk_config_json, extension_start=excluded.extension_start, extension_end=excluded.extension_end, provisioning_agent_url=excluded.provisioning_agent_url, provisioning_agent_secret_encrypted=excluded.provisioning_agent_secret_encrypted, provisioning_agent_timeout_seconds=excluded.provisioning_agent_timeout_seconds, updated_by=excluded.updated_by, updated_at=excluded.updated_at")
            ->execute([(int)post('enabled'), $environment, $mode, $route, $ariUrl, $ariWsUrl, trim((string)post('ari_username')), $ariPasswordEncrypted, trim((string)post('stasis_app', 'ligflow')) ?: 'ligflow', max(5, (int)post('originate_timeout_seconds', 30)), max(5, (int)post('bridge_timeout_seconds', 15)), max(1, (int)post('reconnect_initial_seconds', 2)), max(2, (int)post('reconnect_max_seconds', 30)), $sipWssUrl, $sipDomain, $consultantEndpoint, $webrtcPasswordEncrypted, $webrtcContext, $nvoipTrunk, $directcallTrunk, trim((string)post('nvoip_trunk_config_json', '{}')) ?: '{}', trim((string)post('directcall_trunk_config_json', '{}')) ?: '{}', $extensionStart, $extensionEnd, $agentUrl, $agentSecretEncrypted, $agentTimeout, (int)$user['id']]);
        audit('atualizou_asterisk', 'asterisk_settings:1', null, ['webrtc_password' => $webrtcPasswordChange, 'webrtc_context_changed' => $webrtcContext !== (string)($existing['webrtc_context'] ?? '')]);
        flash('Configuracao Asterisk salva. O modo atual para novas chamadas e ' . $mode . '.');
        redirect('?page=settings#asterisk');
    }

    if (($action === 'save_integration_settings' || $action === 'save_nvoip_settings') && can('settings')) {
        $provider = integration_provider_key((string)post('provider'));
        if ($provider === '') {
            $provider = integration_provider_key((string)post('integration_name'));
        }
        if ($provider === '') {
            flash('Informe um nome ou identificador para a integracao.', 'error');
            redirect('?page=settings&new=1');
        }
        $existing = one("SELECT * FROM integration_settings WHERE company_id = ? AND provider = ?", [$companyId, $provider]);
        $napikey = (string)post('napikey');
        $userToken = (string)post('user_token');
        $numbersip = (string)post('numbersip');
        $userSip = (string)post('user_sip');
        $sipPassword = (string)post('sip_password');
        if ($existing && $napikey === '') {
            $napikey = (string)$existing['napikey'];
        }
        if ($existing && $userToken === '') {
            $userToken = (string)$existing['user_token'];
        }
        if ($existing && $numbersip === '') {
            $numbersip = (string)$existing['numbersip'];
        }
        if ($existing && $userSip === '') {
            $userSip = (string)$existing['user_sip'];
        }
        if ($existing && $sipPassword === '') {
            $sipPassword = (string)$existing['sip_password'];
        } else {
            $sipPassword = encrypt_secret(trim($sipPassword));
        }

        $params = [
            $companyId,
            $provider,
            trim((string)post('integration_name')) ?: strtoupper($provider),
            post('mode', 'simulate'),
            post('auth_method', 'napikey'),
            trim((string)post('api_url')),
            trim($napikey),
            trim($numbersip),
            trim($userSip),
            trim((string)post('sip_wss_url', 'wss://app.nvoip.com.br:7443')),
            trim((string)post('sip_domain', 'app.nvoip.com.br')),
            $sipPassword,
            post('auto_answer_nvoip_callback') ? 1 : 0,
            max(10, (int)post('sip_callback_timeout_seconds', '60')),
            trim($userToken),
            trim((string)post('payload_template')),
            trim((string)post('origin_number')),
            (float)str_replace(',', '.', (string)post('price_per_minute', '0.06')),
            post('recording_enabled') ? 1 : 0,
            trim((string)post('webhook_url')),
            trim((string)post('webhook_secret')),
            trim((string)post('extra_config')),
        ];
        $pdo->prepare("INSERT INTO integration_settings (company_id, provider, integration_name, mode, auth_method, api_url, napikey, numbersip, user_sip, sip_wss_url, sip_domain, sip_password, auto_answer_nvoip_callback, sip_callback_timeout_seconds, user_token, payload_template, origin_number, price_per_minute, recording_enabled, webhook_url, webhook_secret, extra_config)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON CONFLICT(company_id, provider) DO UPDATE SET
                integration_name = excluded.integration_name,
                mode = excluded.mode,
                auth_method = excluded.auth_method,
                api_url = excluded.api_url,
                napikey = excluded.napikey,
                numbersip = excluded.numbersip,
                user_sip = excluded.user_sip,
                sip_wss_url = excluded.sip_wss_url,
                sip_domain = excluded.sip_domain,
                sip_password = excluded.sip_password,
                auto_answer_nvoip_callback = excluded.auto_answer_nvoip_callback,
                sip_callback_timeout_seconds = excluded.sip_callback_timeout_seconds,
                user_token = excluded.user_token,
                payload_template = excluded.payload_template,
                origin_number = excluded.origin_number,
                price_per_minute = excluded.price_per_minute,
                recording_enabled = excluded.recording_enabled,
                webhook_url = excluded.webhook_url,
                webhook_secret = excluded.webhook_secret,
                extra_config = excluded.extra_config,
                updated_at = CURRENT_TIMESTAMP")
            ->execute($params);
        audit('configurou_integracao', 'integration_settings:' . $provider, null, ['provider' => $provider, 'mode' => post('mode'), 'api_url' => post('api_url'), 'origin_number' => post('origin_number')]);
        flash('Integracao salva.');
        redirect('?page=settings&provider=' . rawurlencode($provider));
    }

    if ($action === 'save_sip_diagnostic_config' && can('settings')) {
        $sipPassword = trim((string)post('sip_password'));
        if ($sipPassword === '') {
            flash('Informe a senha SIP para salvar.', 'error');
            redirect('?page=settings&sip=1#diagnostico-sip');
        }
        $existing = one("SELECT * FROM integration_settings WHERE company_id = ? AND provider = 'nvoip'", [$companyId]);
        $sipWssUrl = trim((string)post('sip_wss_url', 'wss://app.nvoip.com.br:7443')) ?: 'wss://app.nvoip.com.br:7443';
        $sipDomain = trim((string)post('sip_domain', 'app.nvoip.com.br')) ?: 'app.nvoip.com.br';
        $userSip = trim((string)post('user_sip'));
        $encryptedPassword = encrypt_secret($sipPassword);

        if ($existing) {
            $pdo->prepare("UPDATE integration_settings
                SET sip_wss_url = ?, sip_domain = ?, user_sip = ?, numbersip = COALESCE(NULLIF(numbersip, ''), ?), sip_password = ?, auto_answer_nvoip_callback = ?, updated_at = CURRENT_TIMESTAMP
                WHERE company_id = ? AND provider = 'nvoip'")
                ->execute([$sipWssUrl, $sipDomain, $userSip, $userSip, $encryptedPassword, post('auto_answer_nvoip_callback') ? 1 : 0, $companyId]);
        } else {
            $defaults = blank_integration_config();
            $pdo->prepare("INSERT INTO integration_settings (company_id, provider, integration_name, mode, auth_method, api_url, napikey, numbersip, user_sip, sip_wss_url, sip_domain, sip_password, auto_answer_nvoip_callback, sip_callback_timeout_seconds, user_token, payload_template, origin_number, price_per_minute, recording_enabled, webhook_url, webhook_secret, extra_config)
                VALUES (?, 'nvoip', 'Nvoip', ?, ?, ?, '', ?, ?, ?, ?, ?, ?, 60, '', ?, '', ?, 1, '', '', '')")
                ->execute([
                    $companyId,
                    $defaults['mode'],
                    $defaults['auth_method'],
                    $defaults['api_url'],
                    $userSip,
                    $userSip,
                    $sipWssUrl,
                    $sipDomain,
                    $encryptedPassword,
                    post('auto_answer_nvoip_callback') ? 1 : 0,
                    $defaults['payload_template'],
                    $defaults['price_per_minute'],
                ]);
        }
        audit('salvou_config_sip_diagnostico', 'integration_settings:nvoip', null, ['has_sip_user' => $userSip !== '', 'has_sip_password' => true]);
        flash('Senha e configuracao SIP salvas.');
        redirect('?page=settings&sip=1#diagnostico-sip');
    }

    if ($action === 'agent_status' && can('agent')) {
        $requestedStatus = (string)post('status');
        $status = $requestedStatus === 'Disponivel' ? 'Discando automatico' : $requestedStatus;
        if (in_array($requestedStatus, ['Pausa', 'Indisponivel'], true)) {
            stop_agent_operation((int)$user['id'], $companyId, $requestedStatus);
        }
        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$status, $user['id']]);
        $pdo->prepare("INSERT INTO agent_sessions (company_id, agent_id, status, current_campaign_id) VALUES (?, ?, ?, ?)")
            ->execute([$companyId, $user['id'], $status, post('campaign_id') ?: null]);
        $startedNext = true;
        if ($requestedStatus === 'Disponivel') {
            $startedNext = start_next_progressive_call((int)post('campaign_id'), (int)$user['id'], $companyId);
            if (!$startedNext) {
                $pdo->prepare("UPDATE users SET status = 'Disponivel' WHERE id = ?")->execute([$user['id']]);
            }
        }
        audit('alterou_status_atendente', 'users:' . $user['id'], null, $_POST);
        if ($requestedStatus !== 'Disponivel' || $startedNext) {
            flash($requestedStatus === 'Disponivel' ? 'Atendimento iniciado. Discando a lista selecionada.' : 'Operacao pausada.');
        }
        redirect('?page=agent&campaign_id=' . (int)post('campaign_id'));
    }

    if ($action === 'reserve_contact' && can('agent')) {
        reserve_next_contact((int)post('campaign_id'), (int)$user['id'], $companyId);
        redirect('?page=agent&campaign_id=' . (int)post('campaign_id'));
    }

    if ($action === 'start_call' && can('agent')) {
        start_call((int)post('campaign_id'), (int)post('contact_id'), (int)$user['id'], $companyId);
        redirect('?page=agent&campaign_id=' . (int)post('campaign_id'));
    }

    if ($action === 'manual_call' && can('agent')) {
        $redirectCampaignId = start_manual_call((int)post('campaign_id'), (string)post('manual_phone'), (int)$user['id'], $companyId);
        redirect('?page=agent&campaign_id=' . $redirectCampaignId);
    }

    if ($action === 'finish_call' && can('agent')) {
        $shouldContinueAuto = post('continue_auto') && (($user['status'] ?? '') === 'Discando automatico');
        finish_call((int)post('call_id'), (int)post('result_id'), (string)post('notes'), $companyId);
        if (post('callback_at')) {
            $call = one('SELECT * FROM calls WHERE id = ? AND company_id = ?', [(int)post('call_id'), $companyId]);
            if ($call) {
                save_callback_for_call($call, (int)$user['id'], (string)post('callback_at'), (string)post('callback_priority', 'normal'), 'Agendamento do consultor', (string)post('notes'));
            }
        }
        $startedNext = false;
        if ($shouldContinueAuto) {
            $pdo->prepare("UPDATE users SET status = 'Discando automatico' WHERE id = ?")->execute([$user['id']]);
            $startedNext = start_next_progressive_call((int)post('campaign_id'), (int)$user['id'], $companyId);
            if (!$startedNext) {
                $pdo->prepare("UPDATE users SET status = 'Disponivel' WHERE id = ?")->execute([$user['id']]);
            } else {
                $nextReserved = one("SELECT phone_e164 FROM contacts WHERE company_id = ? AND reserved_by = ? AND status = 'reservado' ORDER BY reserved_at DESC LIMIT 1", [$companyId, (int)$user['id']]);
                if ($nextReserved && !empty($nextReserved['phone_e164'])) {
                    $_SESSION['auto_next_phone'] = (string)$nextReserved['phone_e164'];
                }
            }
        }
        if (!$shouldContinueAuto || $startedNext) {
            flash('Ligacao registrada e proximo lead liberado.');
        }
        redirect('?page=agent&campaign_id=' . (int)post('campaign_id'));
    }

    if ($action === 'update_answered_call' && can('agent')) {
        update_answered_call((int)post('call_id'), (int)post('result_id'), (string)post('notes'), $companyId, (int)$user['id']);
        if (post('callback_at')) {
            $call = one('SELECT * FROM calls WHERE id = ? AND company_id = ? AND agent_id = ?', [(int)post('call_id'), $companyId, (int)$user['id']]);
            if ($call) {
                save_callback_for_call($call, (int)$user['id'], (string)post('callback_at'), (string)post('callback_priority', 'normal'), 'Agendamento do historico atendido', (string)post('notes'));
            }
        }
        redirect('?page=agent&campaign_id=' . (int)post('campaign_id'));
    }

    if ($action === 'quick_hangup' && can('agent')) {
        quick_hangup((int)post('call_id'), $companyId);
        redirect('?page=agent&campaign_id=' . (int)post('campaign_id'));
    }

    if ($action === 'add_block' && can('blocklist')) {
        $phone = normalize_phone((string)post('phone'));
        if (!$phone) {
            flash('Telefone invalido para bloqueio.', 'error');
            redirect('?page=blocklist');
        }
        $pdo->prepare("INSERT OR IGNORE INTO blocklist (company_id, phone_e164, reason, source, responsible_user_id, notes) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$companyId, $phone, post('reason'), post('source', 'manual'), $user['id'], post('notes')]);
        audit('incluiu_bloqueio', 'blocklist:' . $phone, null, $_POST);
        flash('Numero incluido na lista de bloqueio.');
        redirect('?page=blocklist');
    }

    if ($action === 'update_callback' && can('agent')) {
        $callbackId = (int)post('callback_id');
        $callback = is_platform_admin()
            ? one('SELECT * FROM callbacks WHERE id = ?', [$callbackId])
            : one('SELECT * FROM callbacks WHERE id = ? AND company_id = ?', [$callbackId, $companyId]);
        if (!$callback || (!is_platform_admin() && (int)$callback['agent_id'] !== (int)$user['id'] && !can('supervisor'))) {
            flash('Retorno nao encontrado ou sem permissao para alterar.', 'error');
            redirect('?page=dashboard');
        }
        $callbackStatusValue = strtolower(trim((string)post('callback_status', 'pendente')));
        $selectedResult = null;
        if (str_starts_with($callbackStatusValue, 'resultado:')) {
            $resultId = (int)substr($callbackStatusValue, strlen('resultado:'));
            $selectedResult = one('SELECT * FROM call_results WHERE id = ? AND (company_id = ? OR company_id IS NULL)', [$resultId, (int)$callback['company_id']]);
            if (!$selectedResult) {
                flash('Status de atendimento invalido.', 'error');
                redirect('?page=dashboard');
            }
            $callbackStatus = $selectedResult['action'] === 'agendar_retorno' ? 'pendente' : 'atendido';
        } else {
            $callbackStatus = $callbackStatusValue === 'atendido' ? 'atendido' : 'pendente';
        }
        $scheduledAt = callback_datetime_storage((string)post('callback_at'));
        if ($callbackStatus === 'pendente' && $scheduledAt === '') {
            flash('Informe a nova data e hora para continuar o agendamento.', 'error');
            redirect('?page=dashboard');
        }
        $priority = strtolower(trim((string)post('callback_priority', 'normal')));
        if (!in_array($priority, ['normal', 'alta', 'urgente'], true)) {
            $priority = 'normal';
        }
        if ($selectedResult) {
            $linkedCallId = (int)($callback['call_id'] ?? 0);
            if ($linkedCallId <= 0) {
                $linkedCallId = (int)(one('SELECT id FROM calls WHERE company_id = ? AND agent_id = ? AND contact_id = ? ORDER BY id DESC LIMIT 1', [(int)$callback['company_id'], (int)$callback['agent_id'], (int)$callback['contact_id']])['id'] ?? 0);
            }
            if ($linkedCallId > 0) {
                $linkedCall = one('SELECT notes FROM calls WHERE id = ?', [$linkedCallId]);
                update_answered_call($linkedCallId, (int)$selectedResult['id'], (string)($linkedCall['notes'] ?? ''), (int)$callback['company_id'], (int)$callback['agent_id']);
                if (empty($callback['call_id'])) {
                    db()->prepare('UPDATE callbacks SET call_id = ? WHERE id = ?')->execute([$linkedCallId, $callbackId]);
                }
            }
        }
        db()->prepare("UPDATE callbacks SET scheduled_at = ?, priority = ?, notes = ?, status = ?, completed_at = CASE WHEN ? = 'atendido' THEN datetime('now') ELSE NULL END WHERE id = ?")
            ->execute([$scheduledAt !== '' ? $scheduledAt : $callback['scheduled_at'], $priority, (string)post('callback_notes'), $callbackStatus, $callbackStatus, $callbackId]);
        audit('atualizou_retorno', 'callbacks:' . $callbackId, $callback, ['status' => $callbackStatus, 'scheduled_at' => $scheduledAt, 'priority' => $priority]);
        flash($callbackStatus === 'atendido' ? 'Retorno marcado como atendido.' : 'Retorno reagendado com sucesso.');
        redirect('?page=dashboard');
    }

    if ($action === 'delete_blocklist' && can('blocklist')) {
        $phone = normalize_phone((string)post('phone'));
        if (!$phone) {
            flash('Telefone invalido para exclusao.', 'error');
            redirect('?page=blocklist');
        }
        $entry = one('SELECT * FROM blocklist WHERE company_id = ? AND phone_e164 = ?', [$companyId, $phone]);
        if (!$entry) {
            flash('Numero bloqueado nao encontrado.', 'error');
            redirect('?page=blocklist');
        }
        db()->prepare('DELETE FROM blocklist WHERE company_id = ? AND phone_e164 = ?')->execute([$companyId, $phone]);
        audit('excluiu_bloqueio', 'blocklist:' . $phone, $entry, null);
        flash('Numero removido do bloqueio.');
        redirect('?page=blocklist');
    }

    flash('Acao nao reconhecida ou sem permissao.', 'error');
    redirect('?page=dashboard');
}

function csv_delimiter(string $path): string
{
    $handle = fopen($path, 'rb');
    $lines = [];
    if ($handle) {
        while (($line = fgets($handle)) !== false && count($lines) < 5) {
            $line = trim((string)$line);
            if ($line !== '') {
                $lines[] = $line;
            }
        }
    }
    if ($handle) {
        fclose($handle);
    }
    $counts = [
        ',' => 0,
        ';' => 0,
        "\t" => 0,
    ];
    foreach ($lines as $line) {
        foreach ($counts as $delimiter => $count) {
            $counts[$delimiter] += substr_count($line, $delimiter);
        }
    }
    arsort($counts);
    return (string)array_key_first($counts);
}

function download_csv_template(): never
{
    require_login();
    if (!can('lists')) {
        http_response_code(403);
        exit('Sem permissao.');
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="modelo-contatos-ligflow.csv"');
    $out = fopen('php://output', 'wb');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['nome', 'telefone', 'email', 'empresa', 'documento', 'cidade', 'estado', 'produto', 'origem', 'observacao', 'codigo_externo'], ';');
    fputcsv($out, ['Maria Souza', '41996310725', 'maria@email.com', 'Ademicon', '12345678900', 'Curitiba', 'PR', 'Consorcio imovel', 'Instagram', 'Interessada em carta de 300 mil', 'lead-001'], ';');
    fclose($out);
    exit;
}

function csv_header_key(string $value): string
{
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    return strtolower(trim($value));
}

function csv_field_options(): array
{
    return [
        'ignore' => 'Ignorar',
        'custom' => 'Campo personalizado',
        'name' => 'Nome',
        'phone' => 'Telefone',
        'email' => 'E-mail',
        'organization' => 'Empresa/organizacao',
        'document' => 'Documento',
        'city' => 'Cidade',
        'state' => 'Estado/UF',
        'product' => 'Produto',
        'origin' => 'Origem',
        'notes' => 'Observacao',
        'external_code' => 'Codigo externo',
    ];
}

function guess_csv_field(string $header): string
{
    $key = csv_header_key($header);
    $aliases = [
        'name' => ['nome', 'cliente', 'contato', 'lead', 'nome completo'],
        'phone' => ['telefone', 'celular', 'fone', 'whatsapp', 'numero', 'número', 'phone'],
        'email' => ['email', 'e-mail', 'mail'],
        'organization' => ['empresa', 'organizacao', 'organização', 'escritorio', 'escritório'],
        'document' => ['documento', 'cpf', 'cnpj'],
        'city' => ['cidade', 'municipio', 'município'],
        'state' => ['estado', 'uf'],
        'product' => ['produto', 'interesse', 'servico', 'serviço'],
        'origin' => ['origem', 'fonte'],
        'notes' => ['observacao', 'observação', 'obs', 'nota', 'anotacao', 'anotação'],
        'external_code' => ['codigo_externo', 'código externo', 'codigo', 'código', 'id externo'],
    ];
    foreach ($aliases as $field => $names) {
        if (in_array($key, $names, true)) {
            return $field;
        }
    }
    return 'custom';
}

function prepare_csv_import(int $listId, int $companyId): string
{
    if (empty($_FILES['csv']['tmp_name'])) {
        flash('Selecione um arquivo CSV.', 'error');
        return '';
    }

    $list = one('SELECT id FROM contact_lists WHERE id = ? AND company_id = ?', [$listId, $companyId]);
    if (!$list) {
        flash('Lista invalida para importacao.', 'error');
        return '';
    }

    $file = $_FILES['csv'];
    $token = bin2hex(random_bytes(12));
    $path = IMPORT_DIR . '/' . $token . '.csv';
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        flash('Nao foi possivel salvar o CSV para validacao.', 'error');
        return '';
    }

    $delimiter = csv_delimiter($path);
    $handle = fopen($path, 'rb');
    if (!$handle) {
        flash('Nao foi possivel ler o CSV.', 'error');
        return '';
    }

    $headers = fgetcsv($handle, 0, $delimiter);
    if (!$headers) {
        flash('CSV vazio ou sem cabecalho.', 'error');
        fclose($handle);
        return '';
    }

    $sample = [];
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false && count($sample) < 5) {
        $sample[] = $row;
    }
    fclose($handle);

    $_SESSION['pending_imports'][$token] = [
        'path' => $path,
        'filename' => (string)$file['name'],
        'list_id' => $listId,
        'company_id' => $companyId,
        'delimiter' => $delimiter,
        'headers' => array_map(fn($v) => trim(utf8_text($v)), $headers),
        'sample' => array_map(fn($row) => array_map('utf8_text', $row), $sample),
        'created_at' => time(),
    ];

    flash('Arquivo carregado. Confira os vinculos dos campos antes de importar.');
    return $token;
}

function prepare_new_list_csv_import(int $companyId, int $userId): string
{
    $name = trim((string)post('name'));
    if ($name === '') {
        flash('Informe o nome da nova lista.', 'error');
        return '';
    }
    if (empty($_FILES['csv']['tmp_name'])) {
        flash('Selecione um arquivo CSV para criar a lista.', 'error');
        return '';
    }

    $file = $_FILES['csv'];
    $token = bin2hex(random_bytes(12));
    $path = IMPORT_DIR . '/' . $token . '.csv';
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        flash('Nao foi possivel salvar o CSV para validacao.', 'error');
        return '';
    }

    $delimiter = csv_delimiter($path);
    $handle = fopen($path, 'rb');
    if (!$handle) {
        flash('Nao foi possivel ler o CSV.', 'error');
        return '';
    }

    $headers = fgetcsv($handle, 0, $delimiter);
    if (!$headers) {
        flash('CSV vazio ou sem cabecalho.', 'error');
        fclose($handle);
        @unlink($path);
        return '';
    }

    $sample = [];
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false && count($sample) < 5) {
        $sample[] = $row;
    }
    fclose($handle);

    $_SESSION['pending_imports'][$token] = [
        'path' => $path,
        'filename' => (string)$file['name'],
        'list_id' => 0,
        'company_id' => $companyId,
        'created_by' => $userId,
        'list_data' => [
            'name' => $name,
            'description' => trim((string)post('description')),
            'source' => trim((string)post('source', 'CSV')) ?: 'CSV',
            'tags' => trim((string)post('tags')),
        ],
        'delimiter' => $delimiter,
        'headers' => array_map(fn($v) => trim(utf8_text($v)), $headers),
        'sample' => array_map(fn($row) => array_map('utf8_text', $row), $sample),
        'created_at' => time(),
    ];

    flash('Lista e arquivo preparados. Confira os vinculos dos campos antes de importar.');
    return $token;
}

function confirm_csv_import(string $token, int $listId, int $companyId, int $userId): int
{
    $pending = $_SESSION['pending_imports'][$token] ?? null;
    if (!$pending || (int)$pending['company_id'] !== $companyId || !is_file((string)$pending['path'])) {
        flash('Importacao expirada ou arquivo nao encontrado. Envie o CSV novamente.', 'error');
        return 0;
    }
    if ((int)$pending['list_id'] > 0 && (int)$pending['list_id'] !== $listId) {
        flash('Importacao expirada ou lista invalida. Envie o CSV novamente.', 'error');
        return 0;
    }

    $mapping = is_array(post('field_map', [])) ? post('field_map', []) : [];
    if (!in_array('phone', $mapping, true)) {
        flash('Vincule pelo menos uma coluna como Telefone.', 'error');
        return 0;
    }

    $stats = ['total' => 0, 'imported' => 0, 'duplicated' => 0, 'invalid' => 0, 'blocked' => 0];
    $errors = [];
    $pdo = db();
    $createdNewList = $listId <= 0;
    $pdo->beginTransaction();
    if ($listId <= 0) {
        $listData = is_array($pending['list_data'] ?? null) ? $pending['list_data'] : [];
        $pdo->prepare("INSERT INTO contact_lists (company_id, name, description, source, tags, created_by) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([
                $companyId,
                trim((string)($listData['name'] ?? 'Nova lista')),
                trim((string)($listData['description'] ?? '')),
                trim((string)($listData['source'] ?? 'CSV')) ?: 'CSV',
                trim((string)($listData['tags'] ?? '')),
                $userId,
            ]);
        $listId = (int)$pdo->lastInsertId();
        audit('criou_lista_com_importacao', 'contact_lists:' . $listId, null, $listData);
    }
    $headers = $pending['headers'];
    $handle = fopen((string)$pending['path'], 'rb');
    if (!$handle) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('Nao foi possivel abrir o CSV para importacao.', 'error');
        return 0;
    }
    fgetcsv($handle, 0, (string)$pending['delimiter']);

    while (($row = fgetcsv($handle, 0, (string)$pending['delimiter'])) !== false) {
        $stats['total']++;
        $data = [];
        $custom = [];
        foreach ($headers as $i => $header) {
            $target = (string)($mapping[$i] ?? 'ignore');
            $value = trim(utf8_text($row[$i] ?? ''));
            if ($value === '' || $target === 'ignore') {
                continue;
            }
            if ($target === 'custom') {
                $custom[$header] = $value;
                continue;
            }
            if (!isset($data[$target])) {
                $data[$target] = $value;
            }
        }
        $rawPhone = $data['phone'] ?? '';
        $phone = normalize_phone($rawPhone);
        if (!$phone) {
            $reason = phone_import_error_reason((string)$rawPhone);
            $stats['invalid']++;
            $errors[] = ['linha' => $stats['total'] + 1, 'motivo' => $reason, 'dados' => $data];
            continue;
        }
        if (!is_test_phone_exception($phone) && one('SELECT id FROM blocklist WHERE company_id = ? AND phone_e164 = ?', [$companyId, $phone])) {
            $stats['blocked']++;
            $errors[] = ['linha' => $stats['total'] + 1, 'motivo' => 'Numero bloqueado', 'dados' => $data];
            continue;
        }
        if (one('SELECT id FROM contacts WHERE company_id = ? AND list_id = ? AND phone_e164 = ?', [$companyId, $listId, $phone])) {
            $stats['duplicated']++;
            $errors[] = ['linha' => $stats['total'] + 1, 'motivo' => 'Duplicado', 'dados' => $data];
            continue;
        }

        $pdo->prepare("INSERT INTO contacts (company_id, list_id, name, phone_raw, phone_e164, email, organization, city, state, product, origin, document, external_code, notes, custom_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $companyId,
                $listId,
                $data['name'] ?? '',
                $rawPhone,
                $phone,
                $data['email'] ?? '',
                $data['organization'] ?? '',
                $data['city'] ?? '',
                $data['state'] ?? '',
                $data['product'] ?? '',
                $data['origin'] ?? 'CSV',
                $data['document'] ?? '',
                $data['external_code'] ?? '',
                $data['notes'] ?? '',
                json_encode_safe($custom),
            ]);
        $stats['imported']++;
    }
    fclose($handle);

    if ($stats['imported'] <= 0) {
        $scientificPhones = array_filter($errors, fn($error) => str_contains((string)($error['motivo'] ?? ''), 'formato cientifico'));
        $extraHint = $scientificPhones ? ' Telefones em formato cientifico foram rejeitados; formate a coluna Telefone como texto e envie novamente.' : '';
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $batchListId = $createdNewList ? 0 : $listId;
        db()->prepare("INSERT INTO import_batches (company_id, list_id, filename, total_rows, imported, duplicated, invalid, blocked, error_rows, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$companyId, $batchListId, $pending['filename'], $stats['total'], 0, $stats['duplicated'], $stats['invalid'], $stats['blocked'], json_encode($errors, JSON_UNESCAPED_UNICODE), $userId]);
        @unlink((string)$pending['path']);
        unset($_SESSION['pending_imports'][$token]);
        flash("Importacao cancelada: nenhum contato foi salvo. Corrija o arquivo e importe novamente. {$stats['duplicated']} duplicados, {$stats['invalid']} invalidos, {$stats['blocked']} bloqueados.{$extraHint}", 'error');
        return 0;
    }

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    db()->prepare("INSERT INTO import_batches (company_id, list_id, filename, total_rows, imported, duplicated, invalid, blocked, error_rows, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$companyId, $listId, $pending['filename'], $stats['total'], $stats['imported'], $stats['duplicated'], $stats['invalid'], $stats['blocked'], json_encode($errors, JSON_UNESCAPED_UNICODE), $userId]);
    audit('importou_csv', 'contact_lists:' . $listId, null, $stats);
    @unlink((string)$pending['path']);
    unset($_SESSION['pending_imports'][$token]);
    $scientificPhones = array_filter($errors, fn($error) => str_contains((string)($error['motivo'] ?? ''), 'formato cientifico'));
    $extraHint = $scientificPhones ? ' Telefones em formato cientifico foram rejeitados para evitar importar numeros arredondados pela planilha.' : '';
    $warnBits = [];
    if ($stats['duplicated'] > 0) {
        $warnBits[] = $stats['duplicated'] . ' duplicados';
    }
    if ($stats['invalid'] > 0) {
        $warnBits[] = $stats['invalid'] . ' invalidos';
    }
    if ($stats['blocked'] > 0) {
        $warnBits[] = $stats['blocked'] . ' bloqueados';
    }
    $warnText = $warnBits ? ' (' . implode(', ', $warnBits) . ')' : '';
    $errorCount = $stats['invalid'] + $stats['blocked'];
    $notImportedCount = $stats['duplicated'];
    flash("Importacao com sucesso: {$stats['imported']} importados, {$errorCount} com erro e {$notImportedCount} sem importacao{$warnText}.{$extraHint}");
    return $listId;
}

function update_contact(int $contactId, int $companyId): void
{
    $contact = one('SELECT * FROM contacts WHERE id = ? AND company_id = ? AND status <> "excluido"', [$contactId, $companyId]);
    if (!$contact) {
        flash('Lead nao encontrado.', 'error');
        return;
    }

    $phone = normalize_phone((string)post('phone'));
    if (!$phone) {
        flash('Telefone invalido.', 'error');
        return;
    }

    $duplicate = one('SELECT id FROM contacts WHERE company_id = ? AND list_id = ? AND phone_e164 = ? AND id <> ? AND status <> "excluido"', [$companyId, $contact['list_id'], $phone, $contactId]);
    if ($duplicate) {
        flash('Ja existe outro lead com este telefone.', 'error');
        return;
    }

    db()->prepare("UPDATE contacts SET name = ?, phone_raw = ?, phone_e164 = ?, email = ?, city = ?, state = ?, product = ?, origin = ?, notes = ? WHERE id = ? AND company_id = ?")
        ->execute([
            post('name'),
            post('phone'),
            $phone,
            post('email'),
            post('city'),
            post('state'),
            post('product'),
            post('origin'),
            post('notes'),
            $contactId,
            $companyId,
        ]);
    audit('editou_lead', 'contacts:' . $contactId, $contact, $_POST);
    flash('Lead atualizado.');
}

function create_client_account(int $fallbackCompanyId, int $adminUserId): void
{
    $pdo = db();
    $loginEmail = trim((string)post('login_email'));
    if ($loginEmail === '' || one('SELECT id FROM users WHERE email = ?', [$loginEmail])) {
        flash('Informe um e-mail de login valido e ainda nao cadastrado.', 'error');
        return;
    }

    $clientType = (string)post('client_type', 'Consultor individual');
    $planName = (string)post('plan_name', 'Consultor Individual');
    $plan = one('SELECT * FROM plans WHERE name = ?', [$planName]) ?: one("SELECT * FROM plans WHERE name = 'MVP'");
    $includedMinutes = (int)post('included_minutes', (string)($plan['included_minutes'] ?? 200));
    $maxUsers = (int)post('max_users', (string)($plan['max_users'] ?? 1));
    $maxConsultants = (int)post('max_consultants', (string)($plan['max_consultants'] ?? 1));
    $maxLists = (int)post('max_lists', (string)($plan['max_lists'] ?? 10));
    $maxContacts = (int)post('max_contacts', (string)($plan['max_contacts'] ?? 1000));
    $commercialPrice = (float)str_replace(',', '.', (string)post('commercial_price_per_minute', (string)($plan['commercial_price_per_minute'] ?? 0.35)));
    $companyName = trim((string)post('client_name'));
    $responsibleName = trim((string)post('responsible_name'));
    $mainUserName = trim((string)post('user_name')) ?: $responsibleName ?: $companyName;
    if ($companyName === '' || $mainUserName === '') {
        flash('Informe o nome do cliente e do usuario principal.', 'error');
        return;
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO companies (legal_name, trade_name, cnpj, email, phone, plan, max_users, max_agents, max_channels, monthly_minutes_limit, status, call_window, voip_provider)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $companyName,
                $companyName,
                post('document'),
                post('email') ?: $loginEmail,
                post('whatsapp'),
                $planName,
                $maxUsers,
                $maxConsultants,
                (int)post('max_simultaneous_calls', '1'),
                $includedMinutes,
                post('client_status', 'Ativa'),
                '08:00-18:00',
                'Telefonia gerenciada',
            ]);
        $companyId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO teams (company_id, name, description, daily_goal, simultaneous_limit, priority, voip_queue) VALUES (?, 'Equipe principal', ?, ?, ?, 1, 'principal')")
            ->execute([$companyId, 'Equipe criada automaticamente para ' . $clientType, (int)post('consultant_goal', '0'), (int)post('max_simultaneous_calls', '1')]);
        $teamId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO users (company_id, team_id, name, email, password_hash, role, phone, extension, status, work_hours, created_by)
            VALUES (?, ?, ?, ?, ?, 'cliente_admin', ?, ?, ?, '08:00-18:00', ?)")
            ->execute([$companyId, $teamId, $mainUserName, $loginEmail, password_hash((string)post('password', 'admin123'), PASSWORD_DEFAULT), post('whatsapp'), post('extension'), post('access_status', 'Ativo'), $adminUserId]);
        $userId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO consultant_profiles (company_id, user_id, team_id, display_name, internal_code, status, goal)
            VALUES (?, ?, ?, ?, ?, 'Ativo', ?)")
            ->execute([$companyId, $userId, $teamId, post('consultant_display_name') ?: $mainUserName, post('consultant_code') ?: 'consultor-principal', (int)post('consultant_goal', '0')]);

        $pdo->prepare("INSERT INTO subscriptions (company_id, plan_id, plan_name, starts_at, renews_at, included_minutes, max_users, max_consultants, max_lists, max_contacts, commercial_price_per_minute, status)
            VALUES (?, ?, ?, date('now'), ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$companyId, $plan['id'] ?? null, $planName, post('renews_at') ?: date('Y-m-d', strtotime('+30 days')), $includedMinutes, $maxUsers, $maxConsultants, $maxLists, $maxContacts, $commercialPrice, post('subscription_status', 'Ativa')]);

        $pdo->prepare("INSERT INTO contact_lists (company_id, name, description, source, created_by) VALUES (?, 'Lista principal', 'Lista criada automaticamente para importacao inicial.', 'Sistema', ?)")
            ->execute([$companyId, $adminUserId]);
        $listId = (int)$pdo->lastInsertId();
        $pdo->prepare("INSERT INTO campaigns (company_id, list_id, team_id, name, description, dialer_type, caller_id, sip_trunk, script, max_attempts, status)
            VALUES (?, ?, ?, 'Discador principal', 'Campanha simples criada automaticamente para a lista principal.', 'progressivo', ?, 'Telefonia gerenciada', ?, 1, 'Ativa')")
            ->execute([$companyId, $listId, $teamId, post('origin_number') ?: '', 'Confirme o interesse do contato, registre o resultado e agende retorno quando necessario.']);

        $integrationId = (int)post('integration_id', '0');
        if ($integrationId > 0) {
            $pdo->prepare("INSERT OR IGNORE INTO integration_client_links (company_id, integration_id, origin_rule, max_simultaneous_calls, monthly_minutes_limit, internal_cost_per_minute, commercial_price_per_minute, recording_enabled, calls_enabled, calls_blocked, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Ativo')")
                ->execute([$companyId, $integrationId, post('origin_rule'), (int)post('max_simultaneous_calls', '1'), $includedMinutes, (float)str_replace(',', '.', (string)post('internal_cost_per_minute', '0')), $commercialPrice, post('recording_enabled') ? 1 : 0, post('calls_enabled') ? 1 : 0, post('calls_blocked') ? 1 : 0]);
        }

        $pdo->commit();
        audit('criou_cliente', 'companies:' . $companyId, null, ['client_type' => $clientType, 'plan' => $planName, 'user_id' => $userId]);
        flash('Cliente criado com usuario principal, consultor, equipe padrao, assinatura e discador principal.');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('Nao foi possivel criar o cliente: ' . $e->getMessage(), 'error');
    }
}

function plan_payload(): array
{
    $creditMicros = billing_input_to_micros((string)post('telephony_credit_amount'));
    $rateMicros = billing_input_to_micros((string)post('telephony_rate_per_minute'));
    return [
        'name' => trim((string)post('name')),
        'description' => trim((string)post('description')),
        'included_minutes' => max(0, (int)post('included_minutes', '0')),
        'max_users' => max(1, (int)post('max_users', '1')),
        'max_consultants' => max(1, (int)post('max_consultants', '1')),
        'max_lists' => max(1, (int)post('max_lists', '10')),
        'max_contacts' => max(1, (int)post('max_contacts', '1000')),
        'commercial_price_per_minute' => $rateMicros === null ? null : billing_micros_to_decimal($rateMicros),
        'telephony_credit_micros' => $creditMicros,
        'telephony_rate_micros' => $rateMicros,
        'monthly_price' => (float)str_replace(',', '.', (string)post('monthly_price', '0')),
        'setup_fee' => (float)str_replace(',', '.', (string)post('setup_fee', '0')),
        'billing_period' => (string)post('billing_period', 'Mensal'),
        'payment_type' => (string)post('payment_type', 'Pix'),
        'status' => (string)post('status', 'Ativo'),
    ];
}

function save_plan(): void
{
    $data = plan_payload();
    if ($data['name'] === '') {
        flash('Informe o nome do plano.', 'error');
        return;
    }
    if ($data['telephony_credit_micros'] === null || $data['telephony_rate_micros'] === null) {
        flash('Informe credito e tarifa de telefonia com valores nao negativos.', 'error');
        return;
    }
    if (one('SELECT id FROM plans WHERE name = ?', [$data['name']])) {
        flash('Ja existe um plano com este nome.', 'error');
        return;
    }
    db()->prepare("INSERT INTO plans (name, description, included_minutes, max_users, max_consultants, max_lists, max_contacts, commercial_price_per_minute, telephony_credit_micros, telephony_rate_micros, monthly_price, setup_fee, billing_period, payment_type, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$data['name'], $data['description'], $data['included_minutes'], $data['max_users'], $data['max_consultants'], $data['max_lists'], $data['max_contacts'], $data['commercial_price_per_minute'], $data['telephony_credit_micros'], $data['telephony_rate_micros'], $data['monthly_price'], $data['setup_fee'], $data['billing_period'], $data['payment_type'], $data['status']]);
    audit('criou_plano', 'plans:' . db()->lastInsertId(), null, $data);
    flash('Plano criado.');
}

function update_plan_record(int $planId): void
{
    $plan = one('SELECT * FROM plans WHERE id = ?', [$planId]);
    if (!$plan) {
        flash('Plano nao encontrado.', 'error');
        return;
    }
    $data = plan_payload();
    if ($data['name'] === '') {
        flash('Informe o nome do plano.', 'error');
        return;
    }
    if ($data['telephony_credit_micros'] === null || $data['telephony_rate_micros'] === null) {
        flash('Informe credito e tarifa de telefonia com valores nao negativos.', 'error');
        return;
    }
    if (one('SELECT id FROM plans WHERE name = ? AND id <> ?', [$data['name'], $planId])) {
        flash('Ja existe outro plano com este nome.', 'error');
        return;
    }
    db()->prepare("UPDATE plans SET name = ?, description = ?, included_minutes = ?, max_users = ?, max_consultants = ?, max_lists = ?, max_contacts = ?, commercial_price_per_minute = ?, telephony_credit_micros = ?, telephony_rate_micros = ?, monthly_price = ?, setup_fee = ?, billing_period = ?, payment_type = ?, status = ? WHERE id = ?")
        ->execute([$data['name'], $data['description'], $data['included_minutes'], $data['max_users'], $data['max_consultants'], $data['max_lists'], $data['max_contacts'], $data['commercial_price_per_minute'], $data['telephony_credit_micros'], $data['telephony_rate_micros'], $data['monthly_price'], $data['setup_fee'], $data['billing_period'], $data['payment_type'], $data['status'], $planId]);
    audit('editou_plano', 'plans:' . $planId, $plan, $data);
    flash('Plano atualizado.');
}

function plan_usage_count(array $plan): int
{
    $planId = (int)$plan['id'];
    $planName = (string)$plan['name'];
    $companies = (int)(one('SELECT COUNT(*) total FROM companies WHERE plan = ?', [$planName])['total'] ?? 0);
    $subscriptions = (int)(one('SELECT COUNT(*) total FROM subscriptions WHERE plan_id = ? OR plan_name = ?', [$planId, $planName])['total'] ?? 0);
    return $companies + $subscriptions;
}

function plan_linked_user_count(array $plan): int
{
    return (int)(one("SELECT COUNT(DISTINCT u.id) total
        FROM users u
        INNER JOIN companies c ON c.id=u.company_id
        LEFT JOIN subscriptions s ON s.company_id=c.id
        WHERE s.plan_id=? OR COALESCE(NULLIF(s.plan_name,''),c.plan)=?", [(int)$plan['id'], (string)$plan['name']])['total'] ?? 0);
}

function delete_plan_record(int $planId): void
{
    $plan = one("SELECT * FROM plans WHERE id=? AND status <> 'Removido'", [$planId]);
    if (!$plan) {
        flash('Plano nao encontrado.', 'error');
        return;
    }
    if (plan_linked_user_count($plan) > 0) {
        flash('Este plano possui usuario vinculado e nao pode ser removido.', 'error');
        return;
    }
    if (plan_usage_count($plan) > 0) {
        db()->prepare("UPDATE plans SET status='Removido' WHERE id=?")->execute([$planId]);
        audit('ocultou_plano_com_historico', 'plans:' . $planId, $plan, ['status' => 'Removido']);
        flash('Plano removido da listagem. Os vinculos historicos foram preservados.');
        return;
    }
    db()->prepare('DELETE FROM plans WHERE id=?')->execute([$planId]);
    audit('excluiu_plano', 'plans:' . $planId, $plan, null);
    flash('Plano removido definitivamente.');
}

function client_has_saved_data(int $clientId): bool
{
    $checks = [
        'users',
        'subscriptions',
        'contact_lists',
        'contacts',
        'campaigns',
        'calls',
        'callbacks',
        'payments',
        'telephony_ledger',
    ];
    foreach ($checks as $table) {
        if ((int)scalar("SELECT EXISTS(SELECT 1 FROM {$table} WHERE company_id = ? LIMIT 1)", [$clientId]) === 1) {
            return true;
        }
    }
    return false;
}

function remove_or_hide_client(int $clientId): void
{
    $user = current_user();
    if (!is_platform_admin($user)) {
        flash('Somente o administrador da plataforma pode remover clientes.', 'error');
        return;
    }
    $client = one("SELECT c.* FROM companies c WHERE c.id=? AND c.status <> 'Removida'", [$clientId]);
    if (!$client) {
        flash('Cliente nao encontrado.', 'error');
        return;
    }
    $hasSavedData = client_has_saved_data($clientId);
    db()->prepare("UPDATE companies SET status='Removida' WHERE id=?")->execute([$clientId]);
    audit($hasSavedData ? 'ocultou_cliente_com_historico' : 'removeu_cliente_sem_historico', 'companies:' . $clientId, $client, ['status' => 'Removida']);
    flash($hasSavedData
        ? 'Cliente ocultado da listagem. Usuarios e dados historicos foram preservados.'
        : 'Cliente removido da listagem.');
}

function update_client_account(int $clientId): void
{
    $user = current_user();
    if (!is_platform_admin($user)) {
        flash('Somente o administrador da plataforma pode editar clientes.', 'error');
        return;
    }
    $client = one('SELECT * FROM companies WHERE id = ?', [$clientId]);
    if (!$client) {
        flash('Cliente nao encontrado.', 'error');
        return;
    }

    $planName = (string)post('plan_name', (string)$client['plan']);
    $plan = one('SELECT * FROM plans WHERE name = ?', [$planName]);
    $includedMinutes = (int)post('included_minutes', (string)$client['monthly_minutes_limit']);
    $maxUsers = (int)post('max_users', (string)$client['max_users']);
    $maxConsultants = (int)post('max_consultants', (string)$client['max_agents']);
    $commercialPrice = (float)str_replace(',', '.', (string)post('commercial_price_per_minute', '0'));

    db()->prepare("UPDATE companies SET legal_name = ?, trade_name = ?, cnpj = ?, email = ?, phone = ?, plan = ?, max_users = ?, max_agents = ?, max_channels = ?, monthly_minutes_limit = ?, status = ?, call_window = ? WHERE id = ?")
        ->execute([
            post('legal_name') ?: post('trade_name'),
            post('trade_name'),
            post('document'),
            post('email'),
            post('phone'),
            $planName,
            $maxUsers,
            $maxConsultants,
            (int)post('max_channels', '1'),
            $includedMinutes,
            post('status', 'Ativa'),
            post('call_window', '08:00-18:00'),
            $clientId,
        ]);

    db()->prepare("INSERT INTO subscriptions (company_id, plan_id, plan_name, starts_at, renews_at, included_minutes, max_users, max_consultants, max_lists, max_contacts, commercial_price_per_minute, status)
        VALUES (?, ?, ?, date('now'), ?, ?, ?, ?, ?, ?, ?, ?)
        ON CONFLICT(company_id) DO UPDATE SET
            plan_id = excluded.plan_id,
            plan_name = excluded.plan_name,
            renews_at = excluded.renews_at,
            included_minutes = excluded.included_minutes,
            max_users = excluded.max_users,
            max_consultants = excluded.max_consultants,
            max_lists = excluded.max_lists,
            max_contacts = excluded.max_contacts,
            commercial_price_per_minute = excluded.commercial_price_per_minute,
            status = excluded.status")
        ->execute([
            $clientId,
            $plan['id'] ?? null,
            $planName,
            post('renews_at') ?: null,
            $includedMinutes,
            $maxUsers,
            $maxConsultants,
            (int)post('max_lists', '10'),
            (int)post('max_contacts', '1000'),
            $commercialPrice,
            post('subscription_status', 'Ativa'),
        ]);

    audit('editou_cliente', 'companies:' . $clientId, $client, $_POST);
    flash('Cliente atualizado.');
}

function asterisk_extension_is_active_for_user_status(string $userStatus): bool
{
    return !in_array(trim($userStatus), ['Bloqueado', 'Desconectado', 'Inativo', 'Excluido'], true);
}

function asterisk_default_server_id(): int
{
    return 1;
}

function asterisk_new_users_use_provisioning(): bool
{
    return strtoupper((string)(asterisk_config()['active_mode'] ?? 'NVOIP_DIRECT')) === 'ASTERISK';
}

function asterisk_server_config(int $serverId): array
{
    if ($serverId !== asterisk_default_server_id()) {
        throw new InvalidArgumentException('Servidor Asterisk nao configurado.');
    }
    return asterisk_config();
}

function asterisk_extension_allocation_range(?int $serverId = null): array
{
    $config = asterisk_server_config($serverId ?? asterisk_default_server_id());
    $start = (int)($config['extension_start'] ?? 1000);
    $end = (int)($config['extension_end'] ?? 9999);
    if ($start < 1 || $end < $start || $end > 999999999999999999) {
        throw new RuntimeException('Faixa de ramais Asterisk invalida.');
    }
    return [$start, $end];
}

function asterisk_reserved_extension(): string
{
    $configured = trim((string)(asterisk_config()['consultant_endpoint'] ?? ''));
    if (preg_match('/^(?:PJSIP\/)?([0-9]{1,32})$/i', $configured, $matches) === 1) {
        return $matches[1];
    }
    return '';
}

function asterisk_extension_lifecycle_occupies_number(string $lifecycle): bool
{
    return in_array($lifecycle, ['RESERVED', 'ACTIVE', 'RELEASING'], true);
}

function asterisk_next_available_extension(PDO $pdo, int $companyId, int $serverId): string
{
    [$start, $end] = asterisk_extension_allocation_range($serverId);
    $used = [];
    $statement = $pdo->prepare("SELECT extension, lifecycle_status, status FROM asterisk_user_extensions
        WHERE company_id = ? AND asterisk_server_id = ?");
    $statement->execute([$companyId, $serverId]);
    foreach ($statement->fetchAll() as $row) {
        $lifecycle = strtoupper((string)($row['lifecycle_status'] ?? 'ACTIVE'));
        if ((string)($row['status'] ?? '') === 'Ativo' || asterisk_extension_lifecycle_occupies_number($lifecycle)) {
            $used[(string)$row['extension']] = true;
        }
    }
    $reserved = asterisk_reserved_extension();
    if ($reserved !== '') $used[$reserved] = true;
    for ($extension = $start; $extension <= $end; $extension++) {
        if (!isset($used[(string)$extension])) return (string)$extension;
    }
    throw new RuntimeException('Nao ha ramais disponiveis na faixa configurada.');
}

function asterisk_create_provisioning_job(PDO $pdo, int $companyId, int $userId, int $extensionId, int $serverId, string $extension): int
{
    $idempotencyKey = bin2hex(random_bytes(16));
    $payload = json_encode_safe([
        'extension' => $extension,
        'lifecycle_status' => 'RESERVED',
        'provisioning_version' => 1,
    ]);
    $pdo->prepare("INSERT INTO asterisk_provisioning_jobs
        (company_id, user_id, asterisk_user_extension_id, asterisk_server_id, operation, status, idempotency_key, attempts, payload_json, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'CREATE', 'PENDING', ?, 0, ?, datetime('now'), datetime('now'))")
        ->execute([$companyId, $userId, $extensionId, $serverId, $idempotencyKey, $payload]);
    return (int)$pdo->lastInsertId();
}

function asterisk_reserve_user_extension(PDO $pdo, int $companyId, int $userId, string $requestedExtension, string $userStatus): ?array
{
    if (!asterisk_extension_is_active_for_user_status($userStatus)) return null;
    $requestedExtension = trim($requestedExtension);
    if ($requestedExtension !== '' && preg_match('/^[0-9]{1,32}$/', $requestedExtension) !== 1) {
        throw new InvalidArgumentException('O ramal Asterisk deve conter somente numeros.');
    }

    $serverId = asterisk_default_server_id();
    $extension = $requestedExtension !== '' ? $requestedExtension : asterisk_next_available_extension($pdo, $companyId, $serverId);
    [$rangeStart, $rangeEnd] = asterisk_extension_allocation_range($serverId);
    if ((int)$extension < $rangeStart || (int)$extension > $rangeEnd) {
        throw new InvalidArgumentException('O ramal Asterisk deve estar dentro da faixa configurada para este servidor.');
    }
    if ($extension === asterisk_reserved_extension()) {
        throw new InvalidArgumentException('O ramal configurado para o webphone nao pode ser reservado automaticamente.');
    }
    $duplicateStatement = $pdo->prepare("SELECT id FROM asterisk_user_extensions
        WHERE company_id = ? AND asterisk_server_id = ? AND extension = ?
          AND (status = 'Ativo' OR lifecycle_status IN ('RESERVED', 'ACTIVE', 'RELEASING'))
        LIMIT 1");
    $duplicateStatement->execute([$companyId, $serverId, $extension]);
    if ($duplicateStatement->fetch()) {
        throw new InvalidArgumentException('Este ramal Asterisk ja esta reservado ou vinculado a outro usuario deste cliente.');
    }

    $previousLinks = $pdo->prepare("SELECT id FROM asterisk_user_extensions
        WHERE company_id = ? AND user_id = ? AND asterisk_server_id = ? AND status = 'Ativo'");
    $previousLinks->execute([$companyId, $userId, $serverId]);
    $previousIds = array_map(static fn(array $row): int => (int)$row['id'], $previousLinks->fetchAll());
    if ($previousIds) {
        $placeholders = implode(',', array_fill(0, count($previousIds), '?'));
        $pdo->prepare("UPDATE asterisk_provisioning_jobs
            SET status = 'FAILED', last_error = 'reservation_replaced', completed_at = datetime('now'), updated_at = datetime('now')
            WHERE operation = 'CREATE' AND status = 'PENDING' AND asterisk_user_extension_id IN ({$placeholders})")
            ->execute($previousIds);
    }
    $pdo->prepare("UPDATE asterisk_user_extensions
        SET status = 'Inativo', lifecycle_status = 'RELEASED', released_at = COALESCE(released_at, datetime('now')),
            deactivated_at = COALESCE(deactivated_at, datetime('now')), updated_at = datetime('now')
        WHERE company_id = ? AND user_id = ? AND asterisk_server_id = ? AND status = 'Ativo'")
        ->execute([$companyId, $userId, $serverId]);
    $pdo->prepare("INSERT INTO asterisk_user_extensions
        (company_id, user_id, asterisk_server_id, extension, status, provisioning_status, lifecycle_status, provisioning_version, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'Ativo', 'Pendente', 'RESERVED', 1, datetime('now'), datetime('now'))")
        ->execute([$companyId, $userId, $serverId, $extension]);
    $extensionId = (int)$pdo->lastInsertId();
    $sipPassword = bin2hex(random_bytes(18));
    $pdo->prepare("UPDATE asterisk_user_extensions SET sip_password_encrypted = ? WHERE id = ?")->execute([encrypt_secret($sipPassword), $extensionId]);
    $jobId = asterisk_create_provisioning_job($pdo, $companyId, $userId, $extensionId, $serverId, $extension);
    return ['id' => $extensionId, 'extension' => $extension, 'job_id' => $jobId];
}

function asterisk_update_user_extension(PDO $pdo, int $companyId, int $userId, string $requestedExtension, string $userStatus): ?array
{
    $serverId = asterisk_default_server_id();
    $activeLinks = $pdo->prepare("SELECT id FROM asterisk_user_extensions
        WHERE company_id = ? AND user_id = ? AND asterisk_server_id = ? AND status = 'Ativo'");
    $activeLinks->execute([$companyId, $userId, $serverId]);
    $ids = array_map(static fn(array $row): int => (int)$row['id'], $activeLinks->fetchAll());
    if (!asterisk_extension_is_active_for_user_status($userStatus)) {
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("UPDATE asterisk_provisioning_jobs
                SET status = 'FAILED', last_error = 'reservation_released', completed_at = datetime('now'), updated_at = datetime('now')
                WHERE operation = 'CREATE' AND status = 'PENDING' AND asterisk_user_extension_id IN ({$placeholders})")
                ->execute($ids);
        }
        $pdo->prepare("UPDATE asterisk_user_extensions
            SET status = 'Inativo', lifecycle_status = 'RELEASED', released_at = COALESCE(released_at, datetime('now')),
                deactivated_at = COALESCE(deactivated_at, datetime('now')), updated_at = datetime('now')
            WHERE company_id = ? AND user_id = ? AND asterisk_server_id = ? AND status = 'Ativo'")
            ->execute([$companyId, $userId, $serverId]);
        return null;
    }
    $requestedExtension = trim($requestedExtension);
    $current = one("SELECT id, extension FROM asterisk_user_extensions
        WHERE company_id = ? AND user_id = ? AND asterisk_server_id = ? AND status = 'Ativo'
        ORDER BY id DESC LIMIT 1", [$companyId, $userId, $serverId]);
    if ($current && $requestedExtension !== '' && (string)$current['extension'] === $requestedExtension) {
        return ['id' => (int)$current['id'], 'extension' => (string)$current['extension'], 'job_id' => null];
    }
    return asterisk_reserve_user_extension($pdo, $companyId, $userId, $requestedExtension, $userStatus);
}
function sync_user_asterisk_extension(int $companyId, int $userId, string $extension, string $userStatus): void
{
    $extension = trim($extension);
    if ($extension !== '' && (preg_match('/^[0-9]{1,32}$/', $extension) !== 1)) {
        throw new InvalidArgumentException('O ramal Asterisk deve conter somente numeros.');
    }

    $pdo = db();
    $serverId = asterisk_default_server_id();
    $active = $extension !== '' && asterisk_extension_is_active_for_user_status($userStatus);
    if (!$active) {
        $pdo->prepare("UPDATE asterisk_user_extensions
            SET status = 'Inativo', lifecycle_status = 'RELEASED', released_at = COALESCE(released_at, datetime('now')),
                deactivated_at = COALESCE(deactivated_at, datetime('now')), updated_at = datetime('now')
            WHERE user_id = ? AND asterisk_server_id = ? AND status = 'Ativo'")
            ->execute([$userId, $serverId]);
        return;
    }

    $duplicate = one("SELECT user_id FROM asterisk_user_extensions
        WHERE company_id = ? AND asterisk_server_id = ? AND extension = ? AND status = 'Ativo' AND user_id <> ?", [$companyId, $serverId, $extension, $userId]);
    if ($duplicate) {
        throw new InvalidArgumentException('Este ramal Asterisk ja esta vinculado a outro usuario deste cliente.');
    }

    $pdo->prepare("UPDATE asterisk_user_extensions
        SET status = 'Inativo', lifecycle_status = 'RELEASED', released_at = COALESCE(released_at, datetime('now')),
            deactivated_at = COALESCE(deactivated_at, datetime('now')), updated_at = datetime('now')
        WHERE user_id = ? AND asterisk_server_id = ? AND status = 'Ativo'
          AND (company_id <> ? OR extension <> ?)")
        ->execute([$userId, $serverId, $companyId, $extension]);
    $current = one("SELECT id FROM asterisk_user_extensions
        WHERE company_id = ? AND user_id = ? AND asterisk_server_id = ? AND extension = ? AND status = 'Ativo'", [$companyId, $userId, $serverId, $extension]);
    if ($current) {
        $pdo->prepare("UPDATE asterisk_user_extensions SET lifecycle_status = CASE WHEN lifecycle_status = 'RESERVED' THEN 'RESERVED' ELSE 'ACTIVE' END, updated_at = datetime('now') WHERE id = ?")->execute([(int)$current['id']]);
        return;
    }
    $pdo->prepare("INSERT INTO asterisk_user_extensions
        (company_id, user_id, asterisk_server_id, extension, status, provisioning_status, lifecycle_status, provisioning_version, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'Ativo', 'Pendente', 'ACTIVE', 1, datetime('now'), datetime('now'))")
        ->execute([$companyId, $userId, $serverId, $extension]);
}

function user_access_blocking_relation_counts(int $userId, int $companyId): array
{
    $checks = [
        'users' => ['SELECT COUNT(*) total FROM users WHERE created_by = ? AND id <> ?', [$userId, $userId]],
        'teams' => ['SELECT COUNT(*) total FROM teams WHERE company_id = ? AND supervisor_id = ?', [$companyId, $userId]],
        'contact_lists' => ['SELECT COUNT(*) total FROM contact_lists WHERE company_id = ? AND created_by = ?', [$companyId, $userId]],
        'contacts' => ['SELECT COUNT(*) total FROM contacts WHERE company_id = ? AND reserved_by = ?', [$companyId, $userId]],
        'import_batches' => ['SELECT COUNT(*) total FROM import_batches WHERE company_id = ? AND created_by = ?', [$companyId, $userId]],
        'campaigns' => ['SELECT COUNT(*) total FROM campaigns WHERE company_id = ? AND supervisor_id = ?', [$companyId, $userId]],
        'calls' => ['SELECT COUNT(*) total FROM calls WHERE company_id = ? AND agent_id = ?', [$companyId, $userId]],
        'dial_batches' => ['SELECT COUNT(*) total FROM dial_batches WHERE company_id = ? AND agent_id = ?', [$companyId, $userId]],
        'callbacks' => ['SELECT COUNT(*) total FROM callbacks WHERE company_id = ? AND agent_id = ?', [$companyId, $userId]],
        'blocklist' => ['SELECT COUNT(*) total FROM blocklist WHERE company_id = ? AND responsible_user_id = ?', [$companyId, $userId]],
        'agent_sessions' => ['SELECT COUNT(*) total FROM agent_sessions WHERE company_id = ? AND agent_id = ?', [$companyId, $userId]],
        'audit_logs' => ['SELECT COUNT(*) total FROM audit_logs WHERE user_id = ?', [$userId]],
        'payment_settings' => ['SELECT COUNT(*) total FROM payment_settings WHERE updated_by = ?', [$userId]],
        'google_places_settings' => ['SELECT COUNT(*) total FROM google_places_settings WHERE updated_by = ?', [$userId]],
        'radar_lead_history' => ['SELECT COUNT(*) total FROM radar_lead_history WHERE company_id = ? AND created_by = ?', [$companyId, $userId]],
        'asterisk_settings' => ['SELECT COUNT(*) total FROM asterisk_settings WHERE updated_by = ?', [$userId]],
        'asterisk_user_extensions' => ["SELECT COUNT(*) total
            FROM asterisk_user_extensions axe
            WHERE axe.company_id = ? AND axe.user_id = ?
              AND (
                axe.status <> 'Ativo'
                OR axe.provisioned_at IS NOT NULL
                OR EXISTS (SELECT 1 FROM asterisk_provisioning_jobs apj WHERE apj.asterisk_user_extension_id = axe.id)
              )", [$companyId, $userId]],
        'asterisk_provisioning_jobs' => ['SELECT COUNT(*) total FROM asterisk_provisioning_jobs WHERE company_id = ? AND user_id = ?', [$companyId, $userId]],
        'payments' => ['SELECT COUNT(*) total FROM payments WHERE company_id = ? AND user_id = ?', [$companyId, $userId]],
        'telephony_ledger' => ['SELECT COUNT(*) total FROM telephony_ledger WHERE company_id = ? AND responsible_user_id = ?', [$companyId, $userId]],
    ];
    $counts = [];
    foreach ($checks as $key => [$sql, $params]) {
        $counts[$key] = (int)(one($sql, $params)['total'] ?? 0);
    }
    return array_filter($counts, static fn(int $count): bool => $count > 0);
}

function user_access_can_be_removed(int $userId, int $companyId): bool
{
    $current = current_user();
    if (!$current || (int)($current['id'] ?? 0) === $userId) {
        return false;
    }
    $target = is_platform_admin($current)
        ? one('SELECT id, company_id, role FROM users WHERE id = ? AND deleted_at IS NULL', [$userId])
        : one('SELECT id, company_id, role FROM users WHERE id = ? AND company_id = ? AND deleted_at IS NULL', [$userId, $companyId]);
    if (!$target) {
        return false;
    }
    if (!is_platform_admin($current) && in_array((string)$target['role'], ['admin_geral', 'admin_plataforma'], true)) {
        return false;
    }
    return true;
}

function delete_user_access(int $userId, int $companyId): void
{
    $current = current_user();
    $target = is_platform_admin($current)
        ? one('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL', [$userId])
        : one('SELECT * FROM users WHERE id = ? AND company_id = ? AND deleted_at IS NULL', [$userId, $companyId]);
    if (!$target) {
        flash('Usuario nao encontrado ou fora da sua conta.', 'error');
        return;
    }
    if ((int)($current['id'] ?? 0) === $userId) {
        flash('Voce nao pode remover o proprio acesso.', 'error');
        return;
    }
    if (!is_platform_admin($current) && in_array((string)$target['role'], ['admin_geral', 'admin_plataforma'], true)) {
        flash('Voce nao pode remover um administrador da plataforma.', 'error');
        return;
    }
    $linkedData = user_access_blocking_relation_counts($userId, (int)$target['company_id']);

    $pdo = db();
    try {
        $pdo->beginTransaction();
        if ($linkedData) {
            $pdo->prepare("UPDATE users SET status='Removido', deleted_at=datetime('now') WHERE id=? AND company_id=?")
                ->execute([$userId, (int)$target['company_id']]);
            $pdo->prepare("UPDATE consultant_profiles SET status='Inativo' WHERE company_id=? AND user_id=?")
                ->execute([(int)$target['company_id'], $userId]);
            $pdo->prepare("UPDATE asterisk_user_extensions SET status='Inativo', lifecycle_status='RELEASED', released_at=COALESCE(released_at,datetime('now')), deactivated_at=COALESCE(deactivated_at,datetime('now')), updated_at=datetime('now') WHERE company_id=? AND user_id=? AND status='Ativo'")
                ->execute([(int)$target['company_id'], $userId]);
        } else {
            $pdo->prepare("DELETE FROM consultant_profiles WHERE company_id=? AND user_id=?")
                ->execute([(int)$target['company_id'], $userId]);
            $pdo->prepare("DELETE FROM asterisk_user_extensions WHERE company_id=? AND user_id=?")
                ->execute([(int)$target['company_id'], $userId]);
            $pdo->prepare('DELETE FROM users WHERE id=? AND company_id=?')
                ->execute([$userId, (int)$target['company_id']]);
        }
        $pdo->commit();
        audit($linkedData ? 'ocultou_usuario_com_historico' : 'excluiu_usuario', 'users:' . $userId, $target, ['linked_data' => array_keys($linkedData)]);
        flash($linkedData ? 'Acesso removido da plataforma. O historico foi preservado.' : 'Acesso removido definitivamente.');
    } catch (Throwable) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('Nao foi possivel remover este acesso.', 'error');
    }
}
function update_user_access(int $userId, int $companyId): void
{
    $current = current_user();
    $target = is_platform_admin($current)
        ? one('SELECT * FROM users WHERE id = ?', [$userId])
        : one('SELECT * FROM users WHERE id = ? AND company_id = ?', [$userId, $companyId]);
    if (!$target) {
        flash('Usuario nao encontrado ou fora da sua conta.', 'error');
        return;
    }
    if (!is_platform_admin($current) && in_array($target['role'], ['admin_geral', 'admin_plataforma'], true)) {
        flash('Voce nao pode editar um administrador da plataforma.', 'error');
        return;
    }
    $emailDuplicate = one('SELECT id FROM users WHERE email = ? AND id <> ?', [post('email'), $userId]);
    if ($emailDuplicate) {
        flash('Ja existe outro usuario com este e-mail.', 'error');
        return;
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();

    $newCompanyId = is_platform_admin($current) && post('company_id') ? (int)post('company_id') : (int)$target['company_id'];
    $accessProfileId = is_platform_admin($current) && post('access_profile_id') ? (int)post('access_profile_id') : (int)($target['access_profile_id'] ?? 0);
    $role = (string)$target['role'];
    if ($accessProfileId > 0) {
        $profile = one('SELECT id, role_key FROM access_profiles WHERE id = ? AND (company_id = ? OR company_id IS NULL)', [$accessProfileId, $newCompanyId]);
        if (!$profile) {
            $accessProfileId = 0;
        } else {
            $role = (string)$profile['role_key'];
        }
    }
    if (!is_platform_admin($current)) {
        $role = 'usuario_operacional';
    }
    $allowedModulesJson = is_platform_admin($current) ? selected_modules_json(post('modules', [])) : (string)($target['allowed_modules_json'] ?? '');
    $password = trim((string)post('password'));
    if ($password !== '') {
        db()->prepare("UPDATE users SET company_id = ?, team_id = ?, access_profile_id = ?, name = ?, email = ?, password_hash = ?, role = ?, allowed_modules_json = ?, phone = ?, extension = ?, status = ?, work_hours = ? WHERE id = ?")
            ->execute([$newCompanyId, post('team_id') ?: null, $accessProfileId ?: null, post('name'), post('email'), password_hash($password, PASSWORD_DEFAULT), $role, $allowedModulesJson, post('phone'), post('extension'), post('status'), post('work_hours'), $userId]);
    } else {
        db()->prepare("UPDATE users SET company_id = ?, team_id = ?, access_profile_id = ?, name = ?, email = ?, role = ?, allowed_modules_json = ?, phone = ?, extension = ?, status = ?, work_hours = ? WHERE id = ?")
            ->execute([$newCompanyId, post('team_id') ?: null, $accessProfileId ?: null, post('name'), post('email'), $role, $allowedModulesJson, post('phone'), post('extension'), post('status'), post('work_hours'), $userId]);
    }

    if (one('SELECT id FROM consultant_profiles WHERE user_id = ?', [$userId])) {
        db()->prepare("UPDATE consultant_profiles SET company_id = ?, team_id = ?, display_name = ?, internal_code = ?, status = ?, goal = ? WHERE user_id = ?")
            ->execute([$newCompanyId, post('team_id') ?: null, post('consultant_display_name') ?: post('name'), post('consultant_code') ?: post('extension'), post('consultant_status', 'Ativo'), (int)post('consultant_goal', '0'), $userId]);
    } else {
        db()->prepare("INSERT INTO consultant_profiles (company_id, user_id, team_id, display_name, internal_code, status, goal) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$newCompanyId, $userId, post('team_id') ?: null, post('consultant_display_name') ?: post('name'), post('consultant_code') ?: post('extension'), post('consultant_status', 'Ativo'), (int)post('consultant_goal', '0')]);
    }
    if (asterisk_new_users_use_provisioning()) {
        asterisk_update_user_extension($pdo, $newCompanyId, $userId, (string)post('asterisk_extension'), (string)post('status'));
    } else {
        sync_user_asterisk_extension($newCompanyId, $userId, (string)post('asterisk_extension'), (string)post('status'));
    }
    $pdo->commit();
    audit('editou_usuario', 'users:' . $userId, $target, $_POST);
    flash('Acesso atualizado.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash($e instanceof InvalidArgumentException ? $e->getMessage() : 'Nao foi possivel atualizar o acesso.', 'error');
    }
}

function save_access_profile(int $profileId, int $companyId, int $userId): int
{
    $name = trim((string)post('name'));
    if ($name === '') {
        flash('Informe o nome do perfil.', 'error');
        return 0;
    }
    $roleKey = (string)post('role_key', 'usuario_operacional');
    if (!array_key_exists($roleKey, [
        'cliente_admin' => true,
        'admin_empresa' => true,
        'usuario_operacional' => true,
        'supervisor' => true,
        'atendente' => true,
    ])) {
        $roleKey = 'usuario_operacional';
    }
    $modulesJson = selected_modules_json(post('modules', []));
    if ($profileId > 0) {
        $profile = one('SELECT * FROM access_profiles WHERE id = ? AND company_id = ?', [$profileId, $companyId]);
        if (!$profile) {
            flash('Perfil de acesso nao encontrado.', 'error');
            return 0;
        }
        db()->prepare('UPDATE access_profiles SET name = ?, role_key = ?, modules_json = ? WHERE id = ? AND company_id = ?')
            ->execute([$name, $roleKey, $modulesJson, $profileId, $companyId]);
        audit('editou_perfil_acesso', 'access_profiles:' . $profileId, $profile, $_POST);
        flash('Perfil de acesso atualizado.');
        return $profileId;
    }
    db()->prepare('INSERT INTO access_profiles (company_id, name, role_key, modules_json, created_by) VALUES (?, ?, ?, ?, ?)')
        ->execute([$companyId, $name, $roleKey, $modulesJson, $userId]);
    $newId = (int)db()->lastInsertId();
    audit('criou_perfil_acesso', 'access_profiles:' . $newId, null, $_POST);
    flash('Perfil de acesso criado.');
    return $newId;
}

function delete_access_profile(int $profileId, int $companyId): void
{
    $profile = one('SELECT * FROM access_profiles WHERE id = ? AND company_id = ?', [$profileId, $companyId]);
    if (!$profile) {
        flash('Perfil de acesso nao encontrado.', 'error');
        return;
    }
    $linkedUsers = (int)(one('SELECT COUNT(*) total FROM users WHERE access_profile_id = ?', [$profileId])['total'] ?? 0);
    if ($linkedUsers > 0) {
        flash('Este perfil esta vinculado a usuarios e nao pode ser excluido.', 'error');
        return;
    }
    db()->prepare('DELETE FROM access_profiles WHERE id = ? AND company_id = ?')->execute([$profileId, $companyId]);
    audit('excluiu_perfil_acesso', 'access_profiles:' . $profileId, $profile, null);
    flash('Perfil de acesso excluido.');
}

function avatar_markup(array $user, string $class = 'avatar'): string
{
    $path = (string)($user['avatar_path'] ?? '');
    if ($path !== '' && is_file(__DIR__ . '/' . $path)) {
        return '<span class="' . h($class) . '"><img src="' . h($path) . '" alt=""></span>';
    }
    $name = trim((string)($user['name'] ?? 'U'));
    $parts = preg_split('/\s+/', $name) ?: [];
    $initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
    return '<span class="' . h($class) . ' initials">' . h($initials ?: 'U') . '</span>';
}

function save_avatar_upload(int $userId, string $currentPath): string
{
    if (empty($_FILES['avatar']['tmp_name'])) {
        return $currentPath;
    }
    $file = $_FILES['avatar'];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        flash('Nao foi possivel enviar o avatar.', 'error');
        return $currentPath;
    }
    if ((int)$file['size'] > 2 * 1024 * 1024) {
        flash('O avatar deve ter no maximo 2 MB.', 'error');
        return $currentPath;
    }
    $info = @getimagesize((string)$file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (string)($info['mime'] ?? '');
    if (!isset($allowed[$mime])) {
        flash('Use uma imagem JPG, PNG ou WebP para o avatar.', 'error');
        return $currentPath;
    }
    $target = 'uploads/avatars/user-' . $userId . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    if (!move_uploaded_file((string)$file['tmp_name'], __DIR__ . '/' . $target)) {
        flash('Nao foi possivel salvar o avatar.', 'error');
        return $currentPath;
    }
    return $target;
}

function update_my_account(int $userId): void
{
    $user = one('SELECT * FROM users WHERE id = ?', [$userId]);
    if (!$user) {
        flash('Usuario nao encontrado.', 'error');
        return;
    }
    $emailDuplicate = one('SELECT id FROM users WHERE email = ? AND id <> ?', [post('email'), $userId]);
    if ($emailDuplicate) {
        flash('Ja existe outro usuario com este e-mail.', 'error');
        return;
    }
    $avatarPath = post('remove_avatar') ? '' : save_avatar_upload($userId, (string)($user['avatar_path'] ?? ''));
    $currentPassword = (string)post('current_password');
    $newPassword = trim((string)post('new_password'));
    $confirmPassword = trim((string)post('confirm_password'));
    $changePassword = $currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '';
    if ($changePassword) {
        if (!password_verify($currentPassword, (string)$user['password_hash'])) {
            flash('Senha atual incorreta. Confira a senha ou use a redefinicao por e-mail.', 'error');
            return;
        }
        if ($newPassword === '' || strlen($newPassword) < 6) {
            flash('Informe uma nova senha com pelo menos 6 caracteres.', 'error');
            return;
        }
        if ($newPassword !== $confirmPassword) {
            flash('A nova senha e a confirmacao precisam ser iguais.', 'error');
            return;
        }
        db()->prepare("UPDATE users SET name = ?, email = ?, phone = ?, avatar_path = ?, password_hash = ? WHERE id = ?")
            ->execute([post('name'), post('email'), post('phone'), $avatarPath, password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
    } else {
        db()->prepare("UPDATE users SET name = ?, email = ?, phone = ?, avatar_path = ? WHERE id = ?")
            ->execute([post('name'), post('email'), post('phone'), $avatarPath, $userId]);
    }
    audit('editou_minha_conta', 'users:' . $userId, $user, ['name' => post('name'), 'email' => post('email'), 'avatar' => $avatarPath !== '', 'password_changed' => $changePassword]);
    flash('Sua conta foi atualizada.');
}

function send_password_reset_email(array $user, string $confirmationUrl): bool
{
    $host = preg_replace('/[^A-Za-z0-9.-]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost')) ?: 'localhost';
    $subject = 'Confirmacao de redefinicao de senha - ' . APP_NAME;
    $body = "Ola, " . (string)$user['name'] . ".\n\n"
        . "Recebemos uma solicitacao para redefinir a senha da sua conta no " . APP_NAME . ".\n"
        . "Para confirmar a troca, acesse o link abaixo em ate 30 minutos:\n\n"
        . $confirmationUrl . "\n\n"
        . "Se voce nao solicitou essa alteracao, ignore este e-mail.";
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . APP_NAME . ' <no-reply@' . $host . '>',
    ];
    return @mail((string)$user['email'], $subject, $body, implode("\r\n", $headers));
}

function request_my_password_reset(int $userId): void
{
    $user = one('SELECT * FROM users WHERE id = ?', [$userId]);
    if (!$user) {
        flash('Usuario nao encontrado.', 'error');
        return;
    }
    $newPassword = trim((string)post('reset_password'));
    $confirmPassword = trim((string)post('reset_password_confirm'));
    if ($newPassword === '' || strlen($newPassword) < 6) {
        flash('Informe uma nova senha com pelo menos 6 caracteres.', 'error');
        return;
    }
    if ($newPassword !== $confirmPassword) {
        flash('A nova senha e a confirmacao precisam ser iguais.', 'error');
        return;
    }

    $token = bin2hex(random_bytes(32));
    $pdo = db();
    $pdo->prepare("UPDATE password_reset_requests SET used_at = datetime('now') WHERE user_id = ? AND used_at IS NULL")
        ->execute([$userId]);
    $pdo->prepare("INSERT INTO password_reset_requests (user_id, token_hash, pending_password_hash, expires_at, requested_ip) VALUES (?, ?, ?, ?, ?)")
        ->execute([
            $userId,
            hash('sha256', $token),
            password_hash($newPassword, PASSWORD_DEFAULT),
            gmdate('Y-m-d H:i:s', time() + 1800),
            (string)($_SERVER['REMOTE_ADDR'] ?? ''),
        ]);

    $confirmationUrl = app_public_url('page=password_reset_confirm&token=' . urlencode($token));
    if (!send_password_reset_email($user, $confirmationUrl)) {
        flash('Nao foi possivel enviar o e-mail de confirmacao. Verifique a configuracao de e-mail do servidor.', 'error');
        return;
    }

    audit('solicitou_redefinicao_senha', 'users:' . $userId);
    flash('Enviamos um e-mail de confirmacao para redefinir sua senha.');
}

function confirm_password_reset(string $token): void
{
    $token = trim($token);
    if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        flash('Link de redefinicao invalido.', 'error');
        redirect('?page=login');
    }
    $request = one("SELECT r.*, u.name, u.email FROM password_reset_requests r INNER JOIN users u ON u.id = r.user_id WHERE r.token_hash = ? AND r.used_at IS NULL LIMIT 1", [hash('sha256', $token)]);
    if (!$request) {
        flash('Link de redefinicao invalido ou ja utilizado.', 'error');
        redirect('?page=login');
    }
    $expiresAt = utc_storage_timestamp((string)$request['expires_at']);
    if ($expiresAt === false || $expiresAt < time()) {
        db()->prepare("UPDATE password_reset_requests SET used_at = datetime('now') WHERE id = ?")->execute([(int)$request['id']]);
        flash('Este link de redefinicao expirou. Solicite um novo e-mail.', 'error');
        redirect('?page=login');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([(string)$request['pending_password_hash'], (int)$request['user_id']]);
        $pdo->prepare("UPDATE password_reset_requests SET used_at = datetime('now') WHERE id = ?")
            ->execute([(int)$request['id']]);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }

    audit('confirmou_redefinicao_senha', 'users:' . (int)$request['user_id']);
    unset($_SESSION['user_id']);
    flash('Senha redefinida com sucesso. Entre usando a nova senha.');
    redirect('?page=login');
}

function delete_contact(int $contactId, int $companyId): void
{
    $contact = one('SELECT * FROM contacts WHERE id = ? AND company_id = ? AND status <> "excluido"', [$contactId, $companyId]);
    if (!$contact) {
        flash('Lead nao encontrado.', 'error');
        return;
    }
    db()->prepare("UPDATE contacts SET status = 'excluido', reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE id = ? AND company_id = ?")
        ->execute([$contactId, $companyId]);
    audit('excluiu_lead', 'contacts:' . $contactId, $contact, ['status' => 'excluido']);
    flash('Lead removido da lista.');
}

function reset_contact_list(int $listId, int $companyId): void
{
    $user = current_user();
    if (!is_account_admin($user)) {
        flash('Somente administrador pode resetar listas.', 'error');
        return;
    }

    $list = one('SELECT * FROM contact_lists WHERE id = ? AND company_id = ?', [$listId, $companyId]);
    if (!$list) {
        flash('Lista nao encontrada.', 'error');
        return;
    }

    $affected = db()->prepare("UPDATE contacts
        SET status = 'novo',
            attempts = 0,
            last_call_at = NULL,
            reserved_by = NULL,
            reserved_at = NULL,
            reservation_expires_at = NULL
        WHERE company_id = ?
          AND list_id = ?
          AND status <> 'excluido'");
    $affected->execute([$companyId, $listId]);

    audit('resetou_lista', 'contact_lists:' . $listId, $list, ['contatos_resetados' => $affected->rowCount()]);
    flash('Lista resetada. Os numeros podem ser ligados novamente.');
}

function delete_contact_list(int $listId, int $companyId): void
{
    $user = current_user();
    if (!is_account_admin($user)) {
        flash('Somente administrador pode excluir listas.', 'error');
        return;
    }

    $list = one('SELECT * FROM contact_lists WHERE id = ? AND company_id = ?', [$listId, $companyId]);
    if (!$list) {
        flash('Lista nao encontrada.', 'error');
        return;
    }

    $activity = one("SELECT
            COUNT(*) total_contacts,
            SUM(CASE WHEN attempts > 0 OR last_call_at IS NOT NULL THEN 1 ELSE 0 END) dialed_contacts,
            SUM(CASE WHEN status IN ('reservado','em_ligacao','pos_atendimento') THEN 1 ELSE 0 END) active_contacts
        FROM contacts
        WHERE list_id = ? AND company_id = ? AND status <> 'excluido'", [$listId, $companyId]);
    $dialedContacts = (int)($activity['dialed_contacts'] ?? 0);
    $activeContacts = (int)($activity['active_contacts'] ?? 0);
    if ($dialedContacts > 0 || $activeContacts > 0) {
        flash('Esta lista ja possui numeros com ligacoes iniciadas e nao pode ser excluida.', 'error');
        return;
    }

    db()->prepare('DELETE FROM contacts WHERE list_id = ? AND company_id = ?')->execute([$listId, $companyId]);
    db()->prepare('DELETE FROM contact_lists WHERE id = ? AND company_id = ?')->execute([$listId, $companyId]);
    audit('excluiu_lista', 'contact_lists:' . $listId, $list, null);
    flash('Lista excluida.');
}

function campaign_parallelism_input(mixed $value): int
{
    $raw = trim((string)$value);
    if ($raw === '' || !preg_match('/^(?:[1-9]|10)$/', $raw)) {
        throw new RuntimeException('Ligacoes simultaneas deve ser um numero inteiro entre 1 e 10.');
    }
    return (int)$raw;
}

function active_call_statuses_sql(): string
{
    return "'in_progress','calling_origin','ringing','answered','pos_atendimento'";
}

function live_call_statuses_sql(): string
{
    return "'in_progress','calling_origin','ringing','answered'";
}

function get_active_call(int $agentId, int $companyId): ?array
{
    return one("SELECT * FROM calls WHERE agent_id = ? AND company_id = ? AND status IN (" . active_call_statuses_sql() . ") ORDER BY id DESC LIMIT 1", [$agentId, $companyId]) ?: null;
}

function get_live_call(int $agentId, int $companyId): ?array
{
    return one("SELECT * FROM calls WHERE agent_id = ? AND company_id = ? AND status IN (" . live_call_statuses_sql() . ") ORDER BY id DESC LIMIT 1", [$agentId, $companyId]) ?: null;
}

function call_was_answered(?array $call): bool
{
    if (!$call) {
        return false;
    }
    $status = strtolower(trim((string)($call['status'] ?? '')));
    return in_array($status, ['answered', 'atendida', 'atendido', 'connected', 'em_atendimento', 'pos_atendimento'], true);
}

function is_voicemail_cause(string $cause): bool
{
    $cause = strtolower(trim($cause));
    if ($cause === '') {
        return false;
    }
    return preg_match('/voicemail|mailbox|caixa\s*postal|answering\s*machine|voice\s*mail|machine\s*detected|amd\s*machine/i', $cause) === 1;
}

function is_terminal_call_failure(string $cause): bool
{
    $cause = strtolower(trim($cause));
    if ($cause === '') {
        return false;
    }
    if (is_voicemail_cause($cause)) {
        return true;
    }
    if (preg_match('/\bsip\s*[3-6]\d{2}\b|\b(?:400|402|403|404|405|406|407|408|410|415|416|420|421|423|480|481|482|483|484|485|486|487|488|491|493|500|501|502|503|504|505|513|580|600|603|604|606)\b/i', $cause) === 1) {
        return true;
    }
    return preg_match('/busy(?:\s*here)?|declin|reject|refus|unavailable|temporarily\s*unavailable|no[\s_-]*answer|timeout|timed\s*out|call[\s_-]*failed|failure|forbidden|not\s*found|not\s*reachable|out\s*of\s*coverage|subscriber\s*absent|network\s*(?:error|failure|unavailable)|congestion|invalid\s*(?:number|destination)|number\s*(?:invalid|unallocated)|early[\s_-]*media|session\s*progress\s*sem\s*toque|ringing[\s_-]*not[\s_-]*confirmed|fora\s*d[ae]\s*[aá]rea|sem\s*cobertura|desligad[oa]|n[aã]o\s*dispon[ií]vel|n[uú]mero\s*inv[aá]lido|n[uú]mero\s*inexistente|ocupad[oa]|recusad[oa]|sem\s*sucesso|chamada\s*n[aã]o\s*completada/i', $cause) === 1;
}

function unsuccessful_sip_status(string $cause, bool $stoppedByUser = false): string
{
    if ($stoppedByUser) {
        return 'cancelled';
    }
    $normalized = strtolower(trim($cause));
    if ($normalized === 'no_answer_early_media_timeout') {
        return 'failed';
    }
    if (is_voicemail_cause($normalized)) {
        return 'missed';
    }
    if (preg_match('/\b486\b|busy|ocupad[oa]/i', $normalized) === 1) {
        return 'busy';
    }
    if (preg_match('/\b408\b|no[\s_-]*answer|timeout|timed\s*out|n[aã]o\s*atendeu/i', $normalized) === 1) {
        return 'no_answer';
    }
    return 'failed';
}

function lead_reprocess_base_name(string $name): string
{
    $name = trim(preg_replace('/\s+R\d+$/i', '', trim($name)) ?? '');
    return $name !== '' ? $name : 'Nova remessa';
}

function lead_reprocess_source_name(array $list, int $companyId): string
{
    $campaign = one('SELECT name FROM campaigns WHERE company_id = ? AND list_id = ? ORDER BY id ASC LIMIT 1', [$companyId, (int)($list['id'] ?? 0)]);
    $sourceName = trim((string)($campaign['name'] ?? ''));
    if ($sourceName === '') {
        $sourceName = trim((string)($list['name'] ?? ''));
    }
    return lead_reprocess_base_name($sourceName);
}

function lead_reprocess_status_labels(): array
{
    return [
        'all' => 'Todos',
        'atendida' => 'Atendida',
        'nao_atendida' => 'Nao atendida',
        'ocupada' => 'Ocupada',
        'numero_invalido' => 'Numero invalido',
        'nao_existe' => 'Nao existe',
        'falha_tecnica' => 'Falha tecnica',
        'recusada' => 'Recusada',
        'caixa_postal' => 'Caixa postal',
    ];
}

function suggest_remessa_name(int $companyId, string $sourceName): string
{
    $base = lead_reprocess_base_name($sourceName);
    $existing = rows("SELECT name FROM contact_lists WHERE company_id = ? AND (name = ? OR name LIKE ?)", [$companyId, $base, $base . ' R%']);
    $maxSuffix = 0;
    $hasBase = false;
    $pattern = '/^' . preg_quote($base, '/') . '\s+R(\d+)$/i';
    foreach ($existing as $row) {
        $existingName = trim((string)($row['name'] ?? ''));
        if ($existingName === '') {
            continue;
        }
        if (strcasecmp($existingName, $base) === 0) {
            $hasBase = true;
            continue;
        }
        if (preg_match($pattern, $existingName, $match) === 1) {
            $maxSuffix = max($maxSuffix, (int)$match[1]);
        }
    }

    if (!$hasBase && $maxSuffix === 0) {
        return $base . ' R01';
    }

    return $base . ' R' . str_pad((string)($maxSuffix + 1), 2, '0', STR_PAD_LEFT);
}

function ensure_unique_list_name(int $companyId, string $desiredName): string
{
    $desiredName = trim($desiredName);
    if ($desiredName === '') {
        return 'Nova remessa';
    }

    $existing = one('SELECT id FROM contact_lists WHERE company_id = ? AND name = ?', [$companyId, $desiredName]);
    if (!$existing) {
        return $desiredName;
    }

    return suggest_remessa_name($companyId, $desiredName);
}

function classify_reprocess_lead_status(array $contact, array $payload = []): string
{
    $resultName = strtolower(trim((string)($contact['result_name'] ?? '')));
    $resultAction = strtolower(trim((string)($contact['result_action'] ?? '')));
    $callStatus = strtolower(trim((string)($contact['call_status'] ?? '')));
    $contactStatus = strtolower(trim((string)($contact['status'] ?? '')));
    $cause = strtolower(trim((string)($payload['cause'] ?? $payload['reason'] ?? $payload['message'] ?? $contact['provider_status_raw'] ?? '')));

    if ($callStatus === '' && $resultName === '' && $resultAction === '' && in_array($contactStatus, ['novo', 'retentar'], true)) {
        return 'novo';
    }

    if ($cause !== '' && is_voicemail_cause($cause)) {
        return 'caixa_postal';
    }
    if ($resultName !== '' && str_contains($resultName, 'caixa postal')) {
        return 'caixa_postal';
    }
    if ($resultName === 'ocupado' || $callStatus === 'busy' || preg_match('/\b486\b|busy(?:\s*here)?|ocupad[oa]/i', $cause) === 1) {
        return 'ocupada';
    }
    if (preg_match('/\b(?:404|484)\b|not\s*found|address\s*incomplete|unallocated|not\s*assigned|numero\s*inexistente|nao\s*existe/i', $cause) === 1) {
        return 'nao_existe';
    }
    if (in_array($resultName, ['numero incorreto', 'numero invalido'], true) || ($resultAction === 'bloquear' && str_contains($resultName, 'numero'))) {
        return 'numero_invalido';
    }
    if (($resultName !== '' && (str_contains($resultName, 'nao receber') || str_contains($resultName, 'recus')))
        || preg_match('/\b(?:403|603)\b|declin|reject|refus|recusad[oa]/i', $cause) === 1) {
        return 'recusada';
    }
    if (in_array($callStatus, ['no_answer', 'missed'], true)
        || $resultName === 'nao atendeu'
        || preg_match('/\b(?:408|480)\b|no[\s_-]*answer|ringing[\s_-]*not[\s_-]*confirmed|temporarily\s*unavailable|timeout|timed\s*out|nao\s*atendeu/i', $cause) === 1) {
        return 'nao_atendida';
    }
    if (in_array($callStatus, ['failed', 'cancelled'], true)) {
        return 'falha_tecnica';
    }
    if (in_array($contactStatus, ['pos_atendimento', 'concluido', 'atendida', 'atendido'], true) || in_array($callStatus, ['answered', 'completed'], true)) {
        return 'atendida';
    }

    return 'nao_atendida';
}

function hydrate_reprocess_lead_statuses(array $contacts): array
{
    $payloadByCallId = [];
    $callIds = array_values(array_unique(array_filter(array_map(static fn($contact) => (int)($contact['last_call_id'] ?? 0), $contacts))));
    foreach (array_chunk($callIds, 300) as $callIdChunk) {
        $placeholders = implode(',', array_fill(0, count($callIdChunk), '?'));
        foreach (rows("SELECT call_id, payload FROM call_events WHERE call_id IN ($placeholders) AND event_name IN ('nvoip.webhook', 'sip.ended', 'sip.failed') ORDER BY id DESC", $callIdChunk) as $event) {
            $callId = (int)($event['call_id'] ?? 0);
            if ($callId > 0 && !isset($payloadByCallId[$callId])) {
                $decoded = json_decode((string)($event['payload'] ?? ''), true);
                $payloadByCallId[$callId] = is_array($decoded) ? $decoded : [];
            }
        }
    }
    foreach ($contacts as &$contact) {
        $contact['reprocess_bucket'] = classify_reprocess_lead_status($contact, $payloadByCallId[(int)($contact['last_call_id'] ?? 0)] ?? []);
    }
    unset($contact);
    return $contacts;
}

function list_reprocess_status_counts(int $listId, int $companyId): array
{
    $contacts = rows("
        SELECT c.id, c.status,
               lc.id AS last_call_id, lc.status AS call_status, lc.provider_status_raw,
               COALESCE(cr.name, '') AS result_name, COALESCE(cr.action, '') AS result_action
        FROM contacts c
        LEFT JOIN calls lc ON lc.id = (
            SELECT id FROM calls WHERE company_id = c.company_id AND contact_id = c.id ORDER BY id DESC LIMIT 1
        )
        LEFT JOIN call_results cr ON cr.id = lc.result_id
        WHERE c.list_id = ? AND c.company_id = ? AND c.status <> 'excluido'
    ", [$listId, $companyId]);
    $counts = array_fill_keys(array_keys(lead_reprocess_status_labels()), 0);
    $counts['all'] = count($contacts);
    foreach (hydrate_reprocess_lead_statuses($contacts) as $contact) {
        $bucket = (string)($contact['reprocess_bucket'] ?? '');
        if (array_key_exists($bucket, $counts)) {
            $counts[$bucket]++;
        }
    }
    return $counts;
}

function create_remessa_from_selected_contacts(int $sourceListId, array $selectedIds, int $companyId, int $userId, array $statusFilters, string $remessaName = ''): int
{
    $sourceList = one('SELECT * FROM contact_lists WHERE id = ? AND company_id = ?', [$sourceListId, $companyId]);
    if (!$sourceList) {
        flash('Lista de origem invalida para a remessa.', 'error');
        return 0;
    }

    $selectedIds = array_values(array_unique(array_filter(array_map('intval', $selectedIds), fn($id) => $id > 0)));
    if (!$selectedIds) {
        flash('Selecione ao menos uma lead para criar a nova remessa.', 'error');
        return 0;
    }

    $statusLabels = lead_reprocess_status_labels();
    $allowedStatuses = array_values(array_diff(array_keys($statusLabels), ['all']));
    $statusFilters = array_values(array_unique(array_filter(array_map(static fn($value) => strtolower(trim((string)$value)), $statusFilters), static fn($value) => in_array($value, $allowedStatuses, true))));
    if (!$statusFilters) {
        $statusFilters = $allowedStatuses;
    }

    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $contacts = rows("
        SELECT c.id, c.list_id, c.company_id, c.name, c.phone_raw, c.phone_e164, c.email, c.organization, c.city, c.state,
               c.product, c.origin, c.document, c.external_code, c.notes, c.custom_json, c.status, c.attempts, c.last_call_at,
               lc.id AS last_call_id, lc.status AS call_status, lc.provider_status_raw, lc.answered_at, lc.ended_at,
               COALESCE(cr.name, '') AS result_name, COALESCE(cr.action, '') AS result_action
        FROM contacts c
        LEFT JOIN calls lc ON lc.id = (
            SELECT id
            FROM calls
            WHERE company_id = c.company_id AND contact_id = c.id
            ORDER BY id DESC
            LIMIT 1
        )
        LEFT JOIN call_results cr ON cr.id = lc.result_id
        WHERE c.company_id = ?
          AND c.list_id = ?
          AND c.status <> 'excluido'
          AND c.id IN ($placeholders)
        ORDER BY c.id ASC
    ", array_merge([$companyId, $sourceListId], $selectedIds));

    if (!$contacts) {
        flash('Nenhuma lead selecionada foi encontrada na lista de origem.', 'error');
        return 0;
    }

    $payloadByCallId = [];
    $callIds = [];
    foreach ($contacts as $contact) {
        $callId = (int)($contact['last_call_id'] ?? 0);
        if ($callId > 0) {
            $callIds[] = $callId;
        }
    }
    $callIds = array_values(array_unique($callIds));
    if ($callIds) {
        $eventPlaceholders = implode(',', array_fill(0, count($callIds), '?'));
        $events = rows("SELECT call_id, payload FROM call_events WHERE call_id IN ($eventPlaceholders) AND event_name IN ('nvoip.webhook', 'sip.ended', 'sip.failed') ORDER BY id DESC", $callIds);
        foreach ($events as $event) {
            $callId = (int)($event['call_id'] ?? 0);
            if ($callId > 0 && !isset($payloadByCallId[$callId])) {
                $decoded = json_decode((string)($event['payload'] ?? ''), true);
                $payloadByCallId[$callId] = is_array($decoded) ? $decoded : [];
            }
        }
    }

    $filtered = [];
    foreach ($contacts as $contact) {
        $callId = (int)($contact['last_call_id'] ?? 0);
        $payload = $payloadByCallId[$callId] ?? [];
        $bucket = classify_reprocess_lead_status($contact, $payload);
        if ($statusFilters && !in_array($bucket, $statusFilters, true)) {
            continue;
        }
        $contact['reprocess_bucket'] = $bucket;
        $contact['reprocess_payload'] = $payload;
        $filtered[] = $contact;
    }

    if (count($filtered) !== count($contacts)) {
        flash('Algumas leads selecionadas nao correspondem ao filtro atual. Refaça a selecao com o filtro correto.', 'error');
        return 0;
    }

    if (!$filtered) {
        flash('As leads selecionadas nao correspondem ao filtro atual. Ajuste o filtro e tente novamente.', 'error');
        return 0;
    }

    $sourceName = lead_reprocess_source_name($sourceList, $companyId);
    $desiredName = trim($remessaName) !== '' ? trim($remessaName) : suggest_remessa_name($companyId, $sourceName);
    $newListName = ensure_unique_list_name($companyId, $desiredName);
    $filterLabel = count($statusFilters) === count($allowedStatuses)
        ? 'Todos'
        : implode(', ', array_map(static fn($key) => $statusLabels[$key] ?? $key, $statusFilters));

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $description = sprintf(
            'Remessa criada a partir de %s, filtro %s, %d lead(s) selecionada(s).',
            $sourceName,
            $filterLabel,
            count($filtered)
        );
        $pdo->prepare("INSERT INTO contact_lists (company_id, name, description, source, tags, created_by) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([
                $companyId,
                $newListName,
                $description,
                'Remessa',
                trim((string)($sourceList['tags'] ?? '')),
                $userId,
            ]);
        $newListId = (int)$pdo->lastInsertId();

        $insert = $pdo->prepare("INSERT INTO contacts (company_id, list_id, name, phone_raw, phone_e164, email, organization, city, state, product, origin, document, external_code, notes, custom_json, status, attempts, last_call_at, reserved_by, reserved_at, reservation_expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'novo', 0, NULL, NULL, NULL, NULL)");

        foreach ($filtered as $contact) {
            $custom = [];
            $rawCustom = trim((string)($contact['custom_json'] ?? ''));
            if ($rawCustom !== '') {
                $decodedCustom = json_decode($rawCustom, true);
                if (is_array($decodedCustom)) {
                    $custom = $decodedCustom;
                } else {
                    $custom['raw_custom_json'] = $rawCustom;
                }
            }
            $custom['reprocessamento'] = [
                'origem_lista_id' => (int)$sourceList['id'],
                'origem_lista_nome' => (string)$sourceList['name'],
                'origem_contato_id' => (int)$contact['id'],
                'status_origem' => (string)($contact['status'] ?? ''),
                'tentativas_origem' => (int)($contact['attempts'] ?? 0),
                'ultimo_contato_origem' => (string)($contact['last_call_at'] ?? ''),
                'filtros' => $statusFilters,
                'filtro_label' => $filterLabel,
                'gerado_em' => utc_now_storage(),
            ];

            $insert->execute([
                $companyId,
                $newListId,
                (string)($contact['name'] ?? ''),
                (string)($contact['phone_raw'] ?? $contact['phone_e164'] ?? ''),
                (string)($contact['phone_e164'] ?? ''),
                (string)($contact['email'] ?? ''),
                (string)($contact['organization'] ?? ''),
                (string)($contact['city'] ?? ''),
                (string)($contact['state'] ?? ''),
                (string)($contact['product'] ?? ''),
                (string)($contact['origin'] ?? 'Remessa'),
                (string)($contact['document'] ?? ''),
                (string)($contact['external_code'] ?? ''),
                (string)($contact['notes'] ?? ''),
                json_encode_safe($custom),
            ]);
        }

        $pdo->commit();
        audit('criou_remessa_filtrada', 'contact_lists:' . $newListId, $sourceList, [
            'source_list_id' => $sourceListId,
            'source_list_name' => $sourceList['name'],
            'new_list_name' => $newListName,
            'filters' => $statusFilters,
            'selected_count' => count($filtered),
        ]);
        flash('Nova remessa criada: ' . $newListName . '.');
        return $newListId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('Nao foi possivel criar a nova remessa.', 'error');
        return 0;
    }
}

function normalize_call_status(string $status): string
{
    $normalized = strtolower(trim($status));
    $answered = ['answer', 'answered', 'atendida', 'atendido', 'em_atendimento', 'connected', 'completed_answered'];
    if (in_array($normalized, $answered, true)) {
        return 'answered';
    }
    if (in_array($normalized, ['ringing', 'ring', 'chamando'], true)) {
        return 'ringing';
    }
    if (in_array($normalized, ['calling_origin', 'originando', 'originating'], true)) {
        return 'calling_origin';
    }
    return $status;
}

function call_attempt_status_labels(): array
{
    return [
        'iniciada' => 'Iniciada',
        'chamando' => 'Chamando',
        'atendida' => 'Atendida',
        'nao_atendida' => 'Nao atendida',
        'ocupado' => 'Ocupado',
        'caixa_postal' => 'Caixa postal',
        'numero_inexistente' => 'Numero inexistente',
        'falha' => 'Falha',
        'cancelada' => 'Cancelada',
    ];
}

function normalize_call_attempt_status(string $status, array $context = []): string
{
    $status = strtolower(trim($status));
    $event = strtolower(trim((string)($context['event'] ?? '')));
    $cause = strtolower(trim((string)($context['cause'] ?? '')));
    $reason = strtolower(trim((string)($context['reason'] ?? '')));
    $stack = trim($status . ' ' . $event . ' ' . $cause . ' ' . $reason . ' ' . strtolower((string)json_encode($context, JSON_UNESCAPED_UNICODE)));
    $answeredAt = !empty($context['answered_at']);
    $duration = (int)($context['duration_seconds'] ?? 0);
    $stoppedByUser = !empty($context['stopped_by_user']);

    if ($event === 'start' || in_array($status, ['queued', 'reserved', 'start', 'started', 'initiated'], true)) {
        return 'iniciada';
    }
    if ($event === 'progress' || in_array($status, ['calling_origin', 'originating', 'calling', 'ringing', 'ring', 'in_progress', 'progress', 'dialing'], true)) {
        return 'chamando';
    }
    if ($event === 'answered' || $answeredAt || in_array($status, ['answered', 'connected', 'completed', 'atendida', 'atendido', 'em_atendimento', 'pos_atendimento'], true)) {
        return 'atendida';
    }
    if ($stoppedByUser || in_array($status, ['cancelled', 'canceled', 'cancelada', 'cancelado'], true) || preg_match('/cancel|hangup|stopped by user|user ended|desligad[oa] pelo consultor|manual stop/i', $stack) === 1) {
        return 'cancelada';
    }
    if (is_voicemail_cause($stack) || preg_match('/voicemail|mailbox|caixa\s*postal|answering\s*machine|machine\s*detected|amd|secretari/i', $stack) === 1) {
        return 'caixa_postal';
    }
    if (preg_match('/\b(?:486|600|603)\b|busy|ocupad[oa]/i', $stack) === 1) {
        return 'ocupado';
    }
    if (str_contains($stack, 'no_answer_early_media_timeout')) {
        return 'falha';
    }
    if (preg_match('/no[\s_-]*answer|nao\s*atendeu|missed|timeout|timed\s*out|408|sem\s*resposta/i', $stack) === 1) {
        return 'nao_atendida';
    }
    if (preg_match('/invalid|inexistente|unallocated|not\s*found|not\s*assigned|address\s*incomplete|\b(?:404|484)\b|number\s*invalid|numero\s*invalido|numero\s*inexistente/i', $stack) === 1) {
        return 'numero_inexistente';
    }
    if (preg_match('/out\s*of\s*coverage|fora\s*da\s*area|sem\s*cobertura|not\s*reachable|unavailable|network|failure|failed|error|forbidden|reject|desligad[oa]|fora\s*da\s*cobertura/i', $stack) === 1) {
        return 'falha';
    }
    return $status !== '' ? 'falha' : 'falha';
}

function report_call_filter_clause(string $callAlias, string $contactAlias, int $campaignId, array $filters = []): array
{
    [$tenantClause, $tenantParams] = tenant_clause($callAlias);
    $where = [$tenantClause];
    $params = $tenantParams;

    if ($campaignId > 0) {
        $where[] = "{$callAlias}.campaign_id = ?";
        $params[] = $campaignId;
    }

    $statuses = array_values(array_filter(array_map(static fn($value) => strtolower(trim((string)$value)), (array)($filters['status'] ?? []))));
    $statuses = array_values(array_intersect($statuses, array_keys(call_attempt_status_labels())));
    if ($statuses) {
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $where[] = "COALESCE(NULLIF({$callAlias}.internal_status, ''), NULLIF({$callAlias}.status, '')) IN ({$placeholders})";
        $params = array_merge($params, $statuses);
    }

    $phone = nvoip_phone_digits(trim((string)($filters['phone'] ?? '')));
    if ($phone !== '') {
        if (strlen($phone) === 10 || strlen($phone) === 11) {
            $phone = '55' . $phone;
        }
        $like = '%' . $phone . '%';
        $where[] = "({$callAlias}.destination_number LIKE ? OR {$contactAlias}.phone_e164 LIKE ? OR {$contactAlias}.phone_raw LIKE ?)";
        array_push($params, $like, $like, $like);
    }

    $from = trim((string)($filters['from'] ?? ''));
    $fromUtc = $from !== '' ? local_datetime_to_utc_storage($from . ' 00:00:00') : '';
    if ($fromUtc !== '') {
        $where[] = "{$callAlias}.created_at >= ?";
        $params[] = $fromUtc;
    }

    $to = trim((string)($filters['to'] ?? ''));
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        try {
            $toExclusive = (new DateTimeImmutable($to, new DateTimeZone('America/Sao_Paulo')))->modify('+1 day')->format('Y-m-d');
            $toUtc = local_datetime_to_utc_storage($toExclusive . ' 00:00:00');
            if ($toUtc !== '') {
                $where[] = "{$callAlias}.created_at < ?";
                $params[] = $toUtc;
            }
        } catch (Throwable) {
        }
    }

    return [implode(' AND ', $where), $params];
}

function campaign_call_logs_page(int $campaignId, array $filters = [], int $page = 1, int $perPage = 10): array
{
    $user = current_user();
    if (!$user || !can('reports')) {
        return ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage];
    }

    $page = max(1, $page);
    $perPage = max(5, min(100, $perPage));

    [$whereSql, $params] = report_call_filter_clause('co', 'ct', $campaignId, $filters);
    $total = (int)(one("SELECT COUNT(*) total FROM calls co LEFT JOIN contacts ct ON ct.id = co.contact_id WHERE {$whereSql}", $params)['total'] ?? 0);
    $offset = ($page - 1) * $perPage;
    $rows = rows("
        SELECT co.id, co.created_at, co.started_at, co.answered_at, co.ended_at, co.destination_number, co.origin_number, co.provider, co.external_call_id, co.provider_call_id, co.provider_status_raw, co.internal_status, co.status, co.attempt_number, co.duration_seconds, co.error_message,
               ct.name contato, u.name consultor, ca.name campanha
        FROM calls co
        LEFT JOIN contacts ct ON ct.id = co.contact_id
        LEFT JOIN users u ON u.id = co.agent_id
        LEFT JOIN campaigns ca ON ca.id = co.campaign_id
        WHERE {$whereSql}
        ORDER BY co.id DESC
        LIMIT {$perPage} OFFSET {$offset}
    ", $params);

    return [
        'rows' => $rows,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'pages' => max(1, (int)ceil($total / $perPage)),
    ];
}

function call_modal_payload(int $callId, int $companyId, int $agentId): ?array
{
    $call = one("
        SELECT co.id, co.campaign_id, co.contact_id, co.external_call_id, co.destination_number, co.status, co.started_at, co.answered_at,
               ct.name, ct.phone_e164, ct.email, ct.origin, ct.organization, ct.city, ct.state, ct.product, ct.attempts,
               ca.name campaign_name, ca.dialer_type
        FROM calls co
        LEFT JOIN contacts ct ON ct.id = co.contact_id
        LEFT JOIN campaigns ca ON ca.id = co.campaign_id
        WHERE co.id = ? AND co.company_id = ? AND co.agent_id = ?
        LIMIT 1
    ", [$callId, $companyId, $agentId]);
    if (!$call) {
        return null;
    }

    return [
        'id' => (int)$call['id'],
        'campaign_id' => (int)$call['campaign_id'],
        'contact_id' => (int)$call['contact_id'],
        'external_call_id' => (string)($call['external_call_id'] ?: ''),
        'destination_number' => utf8_text($call['destination_number'] ?: $call['phone_e164']),
        'status' => utf8_text($call['status']),
        'started_at' => (string)($call['started_at'] ?: ''),
        'answered_at' => (string)($call['answered_at'] ?: ''),
        'name' => utf8_text($call['name'] ?: 'Ligacao manual'),
        'phone' => utf8_text($call['phone_e164'] ?: $call['destination_number']),
        'email' => utf8_text($call['email'] ?: ''),
        'origin' => utf8_text($call['organization'] ?: $call['origin'] ?: ''),
        'city_state' => trim(utf8_text($call['city']) . ' / ' . utf8_text($call['state']), ' /'),
        'product' => utf8_text($call['product'] ?: ''),
        'attempts' => (int)($call['attempts'] ?? 0),
        'campaign_name' => utf8_text($call['campaign_name'] ?: ''),
        'dialer_type' => utf8_text($call['dialer_type'] ?: ''),
    ];
}

function campaign_requested_parallelism(array $campaign): int
{
    return max(1, min(10, (int)($campaign['simultaneous_calls'] ?? 1)));
}

function campaign_effective_parallelism(array $campaign, int $companyId): int
{
    $requested = campaign_requested_parallelism($campaign);
    $company = one('SELECT max_channels FROM companies WHERE id = ?', [$companyId]) ?: [];
    $companyCap = max(1, (int)($company['max_channels'] ?? 1));
    $companyInUse = (int)scalar("SELECT COUNT(*) FROM calls WHERE company_id = ? AND status IN (" . live_call_statuses_sql() . ")", [$companyId]);
    $companyAvailable = max(0, $companyCap - $companyInUse);

    $teamAvailable = 10;
    if (!empty($campaign['team_id'])) {
        $team = one('SELECT simultaneous_limit FROM teams WHERE id = ? AND company_id = ?', [(int)$campaign['team_id'], $companyId]) ?: [];
        $teamCap = max(1, (int)($team['simultaneous_limit'] ?? 1));
        $teamInUse = (int)scalar("SELECT COUNT(*) FROM calls co INNER JOIN campaigns ca ON ca.id = co.campaign_id WHERE co.company_id = ? AND ca.team_id = ? AND co.status IN (" . live_call_statuses_sql() . ")", [$companyId, (int)$campaign['team_id']]);
        $teamAvailable = max(0, $teamCap - $teamInUse);
    }

    $eligible = (int)scalar("SELECT COUNT(*) FROM contacts c WHERE c.company_id = ? AND c.list_id = ? AND c.status = 'novo' AND c.attempts = 0 AND c.last_call_at IS NULL AND (c.reservation_expires_at IS NULL OR c.reservation_expires_at < datetime('now')) AND NOT EXISTS (SELECT 1 FROM blocklist b WHERE b.company_id = c.company_id AND b.phone_e164 = c.phone_e164)", [$companyId, (int)$campaign['list_id']]);
    return min($requested, 10, $companyAvailable, $teamAvailable, $eligible);
}

function campaign_uses_asterisk_parallelism(array $campaign, int $companyId): bool
{
    $config = asterisk_config();
    return !empty($config['enabled'])
        && ($config['active_mode'] ?? '') === 'ASTERISK'
        && campaign_requested_parallelism($campaign) > 1;
}
function active_dial_batch(int $agentId, int $companyId): ?array
{
    return one("SELECT * FROM dial_batches WHERE company_id = ? AND agent_id = ? AND status IN ('ORIGINATING','RINGING','WINNER','CONNECTED') ORDER BY id DESC LIMIT 1", [$companyId, $agentId]) ?: null;
}

function agent_parallel_batch_state(int $agentId, int $companyId): ?array
{
    $batch = active_dial_batch($agentId, $companyId);
    if (!$batch || (int)($batch['requested_parallelism'] ?? 1) <= 1) return null;
    $counts = one("SELECT
            COUNT(*) originated_count,
            COALESCE(SUM(CASE WHEN finalized_at IS NULL AND status IN ('in_progress','calling_origin','ringing','answered','connected') THEN 1 ELSE 0 END), 0) active_count,
            COALESCE(SUM(CASE WHEN finalized_at IS NULL AND status = 'ringing' THEN 1 ELSE 0 END), 0) ringing_count,
            COALESCE(SUM(CASE WHEN answered_at IS NOT NULL OR race_outcome IN ('WINNER','LATE_ANSWERED') THEN 1 ELSE 0 END), 0) answered_count,
            COALESCE(SUM(CASE WHEN finalized_at IS NOT NULL THEN 1 ELSE 0 END), 0) finalized_count
        FROM calls WHERE company_id = ? AND dial_batch_id = ?", [$companyId, (int)$batch['id']]) ?: [];
    $campaignName = (string)(scalar('SELECT name FROM campaigns WHERE id = ? AND company_id = ?', [(int)$batch['campaign_id'], $companyId]) ?: '');
    return [
        'batch' => $batch,
        'batch_id' => (int)$batch['id'],
        'campaign_name' => $campaignName,
        'requested_count' => (int)$batch['requested_parallelism'],
        'effective_count' => (int)$batch['effective_parallelism'],
        'originated_count' => (int)($counts['originated_count'] ?? 0),
        'active_count' => (int)($counts['active_count'] ?? 0),
        'ringing_count' => (int)($counts['ringing_count'] ?? 0),
        'answered_count' => (int)($counts['answered_count'] ?? 0),
        'finalized_count' => (int)($counts['finalized_count'] ?? 0),
        'winner_call_id' => (int)($batch['winner_call_id'] ?? 0),
        'awaiting_winner' => empty($batch['winner_call_id']) && (int)($counts['active_count'] ?? 0) > 0 && in_array((string)$batch['status'], ['ORIGINATING', 'RINGING'], true),
    ];
}

function insert_dial_batch(PDO $pdo, array $batch): int
{
    $available = array_flip(array_column($pdo->query('PRAGMA table_info(dial_batches)')->fetchAll(), 'name'));
    $values = [
        'company_id' => (int)$batch['company_id'],
        'campaign_id' => (int)$batch['campaign_id'],
        'agent_id' => (int)$batch['agent_id'],
        'requested_parallelism' => (int)$batch['requested_parallelism'],
        'effective_parallelism' => (int)$batch['effective_parallelism'],
        'telephony_mode' => (string)$batch['telephony_mode'],
        'telephony_trunk' => (string)$batch['telephony_trunk'],
        'status' => (string)$batch['status'],
        'idempotency_key' => (string)$batch['idempotency_key'],
    ];
    foreach ([
        'trunk_route' => 'telephony_trunk',
        'requested_calls' => 'requested_parallelism',
        'effective_calls' => 'effective_parallelism',
    ] as $legacyColumn => $sourceColumn) {
        if (isset($available[$legacyColumn])) {
            $values[$legacyColumn] = $values[$sourceColumn];
        }
    }
    $values = array_intersect_key($values, $available);
    $columns = array_keys($values);
    $pdo->prepare('INSERT INTO dial_batches (' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')')
        ->execute(array_values($values));
    return (int)$pdo->lastInsertId();
}

function reserve_parallel_contacts(array $campaign, int $agentId, int $companyId, int $limit, int $telephonyPeriodId): array
{
    $pdo = db();
    $pdo->exec('BEGIN IMMEDIATE');
    $transactionActive = true;
    try {
        if (active_dial_batch($agentId, $companyId)) {
            $pdo->exec('COMMIT');
            $transactionActive = false;
            return [];
        }
        $candidates = rows("SELECT c.* FROM contacts c WHERE c.company_id = ? AND c.list_id = ? AND c.status = 'novo' AND c.attempts = 0 AND c.last_call_at IS NULL AND (c.reservation_expires_at IS NULL OR c.reservation_expires_at < datetime('now')) AND NOT EXISTS (SELECT 1 FROM blocklist b WHERE b.company_id = c.company_id AND b.phone_e164 = c.phone_e164) ORDER BY c.id ASC LIMIT " . (int)$limit, [$companyId, (int)$campaign['list_id']]);
        if (!$candidates) {
            $pdo->exec('COMMIT');
            $transactionActive = false;
            return [];
        }
        $config = asterisk_config();
        $mode = 'ASTERISK';
        $trunk = (new AsteriskProvider($config))->trunk();
        $key = 'batch-' . bin2hex(random_bytes(16));
        $batchId = insert_dial_batch($pdo, [
            'company_id' => $companyId,
            'campaign_id' => (int)$campaign['id'],
            'agent_id' => $agentId,
            'requested_parallelism' => max(1, min(10, (int)($campaign['simultaneous_calls'] ?? 1))),
            'effective_parallelism' => $limit,
            'telephony_mode' => $mode,
            'telephony_trunk' => $trunk,
            'status' => 'ORIGINATING',
            'idempotency_key' => $key,
        ]);
        $reserved = [];
        foreach ($candidates as $contact) {
            $guard = $pdo->prepare("UPDATE contacts SET reserved_by=?, reserved_at=datetime('now'), reservation_expires_at=datetime('now','+10 minutes'), status='em_ligacao', attempts=attempts+1, last_call_at=datetime('now') WHERE id=? AND company_id=? AND status='novo' AND attempts=0 AND last_call_at IS NULL");
            $guard->execute([$agentId, (int)$contact['id'], $companyId]);
            if ($guard->rowCount() !== 1) continue;
            $externalId = 'ARI-' . bin2hex(random_bytes(12));
            $pdo->prepare("INSERT INTO calls (company_id,campaign_id,contact_id,agent_id,provider,external_call_id,provider_call_id,destination_number,status,provider_status_raw,internal_status,attempt_number,billing_rate_micros,telephony_period_id,telephony_mode,telephony_trunk,provider_channel_id,dial_batch_id,race_outcome,started_at,ringing_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,datetime('now'))")
                ->execute([$companyId, (int)$campaign['id'], (int)$contact['id'], $agentId, 'Asterisk ARI', $externalId, $externalId, (string)$contact['phone_e164'], 'in_progress', 'ARI_ORIGINATING', 'iniciada', max(1, (int)$contact['attempts'] + 1), call_plan_rate_micros($companyId), $telephonyPeriodId, 'ASTERISK', $trunk, $externalId, $batchId, 'PENDING']);
            $contact['call_id'] = (int)$pdo->lastInsertId();
            $contact['external_call_id'] = $externalId;
            $reserved[] = $contact;
        }
        if (!$reserved) { $pdo->prepare("UPDATE dial_batches SET status='NO_WINNER', updated_at=datetime('now') WHERE id=?")->execute([$batchId]); }
        $pdo->exec('COMMIT');
        $transactionActive = false;
        return ['id' => $batchId, 'contacts' => $reserved, 'trunk' => $trunk];
    } catch (Throwable $e) {
        if ($transactionActive) {
            try {
                $pdo->exec('ROLLBACK');
            } catch (Throwable) {
            }
        }
        throw $e;
    }
}

function asterisk_user_extension_record(int $companyId, int $userId): ?array
{
    return one("SELECT * FROM asterisk_user_extensions
        WHERE company_id=? AND user_id=? AND asterisk_server_id=1 AND status='Ativo'
        ORDER BY id DESC LIMIT 1", [$companyId, $userId]);
}

function asterisk_user_extension_ready(?array $extension): bool
{
    return $extension
        && strtoupper((string)($extension['lifecycle_status'] ?? '')) === 'ACTIVE'
        && strtolower((string)($extension['provisioning_status'] ?? '')) === 'concluido'
        && !empty($extension['provisioned_at'])
        && !empty($extension['sip_password_encrypted']);
}

function asterisk_uses_local_ari(array $config): bool
{
    foreach (['ari_url', 'ari_ws_url'] as $key) {
        $host = strtolower((string)(parse_url((string)($config[$key] ?? ''), PHP_URL_HOST) ?? ''));
        if (!in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }
    }
    return true;
}

function start_asterisk_parallel_batch(array $campaign, int $agentId, int $companyId): bool
{
    $asterisk = asterisk_config();
    if (!asterisk_uses_local_ari($asterisk)) {
        $extension = asterisk_user_extension_record($companyId, $agentId);
        if (!asterisk_user_extension_ready($extension)) {
            $extensionLabel = !empty($extension['extension']) ? ' ' . (string)$extension['extension'] : '';
            flash('Ramal Asterisk' . $extensionLabel . ' ainda nao foi provisionado. Atendimento automatico nao iniciado.', 'error');
            return false;
        }
    }
    $allowed = telephony_call_allowed($companyId);
    if (!$allowed['ok']) { flash((string)$allowed['message'], 'error'); return false; }
    $limit = campaign_effective_parallelism($campaign, $companyId);
    if ($limit < 1) { flash('Sem capacidade ou leads elegiveis para iniciar o lote Asterisk.', 'error'); return false; }
    $batch = reserve_parallel_contacts($campaign, $agentId, $companyId, $limit, (int)$allowed['state']['period_id']);
    if (!$batch) { flash('Ja existe um lote Asterisk ativo para este consultor.', 'error'); return false; }
    if (empty($batch['contacts'])) { flash('Nao ha numeros novos para ligar nesta lista.', 'error'); return false; }
    $agent = one('SELECT * FROM users WHERE id = ? AND company_id = ?', [$agentId, $companyId]) ?: [];
    $provider = telephony_provider_for_company($companyId);
    if (!$provider instanceof AsteriskProvider) throw new RuntimeException('Provedor Asterisk indisponivel para este lote.');
    $originatedCount = 0;
    foreach ($batch['contacts'] as $contact) {
        try {
            $originated = $provider->originateParallel($campaign, $contact, $agent, (string)$contact['external_call_id']);
            db()->prepare("UPDATE calls SET provider_channel_id=?, provider_linked_id=?, provider_status_raw='ARI_ORIGINATING', last_event_at=datetime('now') WHERE id=? AND dial_batch_id=?")
                ->execute([(string)$originated['provider_channel_id'], (string)$originated['provider_linked_id'], (int)$contact['call_id'], (int)$batch['id']]);
            $originatedCount++;
        } catch (Throwable $e) {
            db()->prepare("UPDATE calls SET status='failed', internal_status='falha', provider_status_raw='ARI_ORIGINATE_FAILED', error_message=?, ended_at=datetime('now'), finalized_at=datetime('now'), race_outcome='ORIGINATE_FAILED' WHERE id=? AND dial_batch_id=? AND finalized_at IS NULL")
                ->execute([$e->getMessage(), (int)$contact['call_id'], (int)$batch['id']]);
            db()->prepare("UPDATE contacts SET status='concluido', reserved_by=NULL, reserved_at=NULL, reservation_expires_at=NULL WHERE id=?")->execute([(int)$contact['id']]);
        }
    }
    asterisk_continue_batch_if_exhausted((int)$batch['id']);
    if ($originatedCount === 0) {
        flash('Nenhuma chamada foi iniciada. Atendimento automatico encerrado.', 'error');
        return false;
    }
    db()->prepare("UPDATE users SET status='Discando automatico' WHERE id=? AND company_id=?")->execute([$agentId, $companyId]);
    audit('iniciou_lote_asterisk', 'dial_batches:' . (int)$batch['id'], null, ['parallelism' => $limit]);
    flash('Lote Asterisk iniciado com ' . $originatedCount . ' chamadas.');
    return true;
}

function asterisk_batch_answered(int $callId): void
{
    $pdo = db(); $pdo->beginTransaction();
    try {
        $call = one('SELECT * FROM calls WHERE id=?', [$callId]);
        if (!$call || empty($call['dial_batch_id'])) { $pdo->commit(); return; }
        $batch = one('SELECT * FROM dial_batches WHERE id=? AND company_id=?', [(int)$call['dial_batch_id'], (int)$call['company_id']]);
        if (!$batch || !in_array((string)$batch['status'], ['ORIGINATING','RINGING'], true)) { $pdo->commit(); return; }
        $winner = $pdo->prepare("UPDATE dial_batches SET winner_call_id=?, status='WINNER', updated_at=datetime('now') WHERE id=? AND winner_call_id IS NULL AND status IN ('ORIGINATING','RINGING')");
        $winner->execute([$callId, (int)$batch['id']]);
        if ($winner->rowCount() !== 1) {
            $pdo->prepare("UPDATE calls SET race_outcome='LATE_ANSWERED' WHERE id=?")->execute([$callId]);
            $pdo->commit();
            try { telephony_provider_for_company((int)$call['company_id'])->hangup($call); } catch (Throwable) { }
            return;
        }
        $pdo->prepare("UPDATE calls SET race_outcome='WINNER' WHERE id=?")->execute([$callId]);
        $losers = rows("SELECT * FROM calls WHERE dial_batch_id=? AND id<>? AND finalized_at IS NULL", [(int)$batch['id'], $callId]);
        $pdo->prepare("UPDATE calls SET race_outcome='LOSER' WHERE dial_batch_id=? AND id<>? AND finalized_at IS NULL")->execute([(int)$batch['id'], $callId]);
        $pdo->commit();
        $provider = telephony_provider_for_company((int)$call['company_id']);
        foreach ($losers as $loser) { try { $provider->hangup($loser); } catch (Throwable) { } }
        if ($provider instanceof AsteriskProvider) {
            $agent = one('SELECT * FROM users WHERE id=? AND company_id=?', [(int)$call['agent_id'], (int)$call['company_id']]) ?: [];
            $bridge = $provider->connectParallelWinner($call, $agent);
            db()->prepare("UPDATE calls SET provider_bridge_id=?, internal_status='conectada', status='connected', connected_at=COALESCE(connected_at,datetime('now')) WHERE id=?")->execute([(string)$bridge['bridge_id'], $callId]);
            db()->prepare("UPDATE dial_batches SET status='CONNECTED', updated_at=datetime('now') WHERE id=?")->execute([(int)$batch['id']]);
            if (filter_var(env_value('ASTERISK_BRIDGE_RECORDING_HOMOLOGATION'), FILTER_VALIDATE_BOOLEAN)) {
                $recording = asterisk_try_winner_bridge_recording(
                    static fn(string $bridgeId, string $recordingName, string $format): array => $provider->recordBridge($bridgeId, $recordingName, $format),
                    array_replace($call, ['race_outcome' => 'WINNER']),
                    (string)$bridge['bridge_id']
                );
                if (empty($recording['started'])) {
                    error_log('Falha ao iniciar gravacao ARI da chamada vencedora ' . $callId . ': ' . (string)($recording['error'] ?? $recording['skipped'] ?? 'erro desconhecido'));
                } else {
                    $recordingResult = (array)($recording['result'] ?? []);
                    error_log('Gravacao ARI iniciada: call_id=' . $callId . ' bridge_id=' . (string)$bridge['bridge_id'] . ' arquivo=' . (string)($recordingResult['filename'] ?? ''));
                }
            }
        }
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}

function asterisk_continue_batch_if_exhausted(int $batchId): void
{
    $batch = one('SELECT * FROM dial_batches WHERE id=?', [$batchId]);
    if (!$batch || !empty($batch['winner_call_id'])) return;
    $live = (int)scalar("SELECT COUNT(*) FROM calls WHERE dial_batch_id=? AND finalized_at IS NULL AND status IN ('in_progress','calling_origin','ringing','answered','connected')", [$batchId]);
    if ($live > 0) return;
    db()->prepare("UPDATE dial_batches SET status='NO_WINNER', next_started_at=COALESCE(next_started_at,datetime('now')), updated_at=datetime('now') WHERE id=? AND winner_call_id IS NULL")
        ->execute([$batchId]);
    db()->prepare("UPDATE contacts SET status='concluido', reserved_by=NULL, reserved_at=NULL, reservation_expires_at=NULL WHERE id IN (SELECT contact_id FROM calls WHERE dial_batch_id=?) AND status IN ('reservado','em_ligacao')")
        ->execute([$batchId]);
    db()->prepare("UPDATE users SET status='Disponivel' WHERE id=? AND company_id=? AND status='Discando automatico'")
        ->execute([(int)$batch['agent_id'], (int)$batch['company_id']]);
}

function cancel_active_asterisk_batch(int $agentId, int $companyId): void
{
    $batch = active_dial_batch($agentId, $companyId);
    if (!$batch) return;
    $calls = rows("SELECT * FROM calls WHERE dial_batch_id=? AND finalized_at IS NULL", [(int)$batch['id']]);
    db()->prepare("UPDATE dial_batches SET status='CANCELLED', updated_at=datetime('now') WHERE id=?")->execute([(int)$batch['id']]);
    db()->prepare("UPDATE calls SET status='cancelled', internal_status='cancelada', race_outcome=COALESCE(NULLIF(race_outcome,''),'CANCELLED'), ended_at=datetime('now'), finalized_at=datetime('now') WHERE dial_batch_id=? AND finalized_at IS NULL")->execute([(int)$batch['id']]);
    db()->prepare("UPDATE contacts SET status='concluido', reserved_by=NULL, reserved_at=NULL, reservation_expires_at=NULL WHERE id IN (SELECT contact_id FROM calls WHERE dial_batch_id = ?)")->execute([(int)$batch['id']]);
    foreach ($calls as $call) { try { telephony_provider_for_company($companyId)->hangup($call); } catch (Throwable) { } }
}

function next_eligible_contact(array $campaign, int $companyId): ?array
{
    return one("
        SELECT c.* FROM contacts c
        WHERE c.company_id = ?
          AND c.list_id = ?
          AND c.status = 'novo'
          AND c.attempts = 0
          AND c.last_call_at IS NULL
          AND (c.reservation_expires_at IS NULL OR c.reservation_expires_at < datetime('now'))
          AND NOT EXISTS (SELECT 1 FROM blocklist b WHERE b.company_id = c.company_id AND b.phone_e164 = c.phone_e164)
        ORDER BY c.id ASC
        LIMIT 1
    ", [$companyId, $campaign['list_id']]) ?: null;
}

function start_next_progressive_call(int $campaignId, int $agentId, int $companyId): bool
{
    $campaign = one('SELECT * FROM campaigns WHERE id = ? AND company_id = ? AND status = "Ativa"', [$campaignId, $companyId]);
    if (!$campaign) {
        flash('Campanha indisponivel.', 'error');
        return false;
    }
    if (campaign_uses_asterisk_parallelism($campaign, $companyId)) {
        return start_asterisk_parallel_batch($campaign, $agentId, $companyId);
    }
    if (get_live_call($agentId, $companyId)) {
        flash('Ja existe uma chamada ativa para este consultor.', 'error');
        return false;
    }
    $contact = next_eligible_contact($campaign, $companyId);
    if (!$contact) {
        flash('Nao ha numeros novos para ligar nesta lista.', 'error');
        return false;
    }
    db()->prepare("UPDATE contacts SET reserved_by = ?, reserved_at = datetime('now'), reservation_expires_at = datetime('now', '+10 minutes'), status = 'reservado' WHERE id = ?")
        ->execute([$agentId, $contact['id']]);
    audit('discador_reservou_contato', 'contacts:' . $contact['id']);
    flash('Lead reservado. O webfone vai iniciar a ligacao SIP.');
    return true;
}

function stop_agent_operation(int $agentId, int $companyId, string $status): void
{
    cancel_active_asterisk_batch($agentId, $companyId);
    $active = get_active_call($agentId, $companyId);
    if ($active) {
        if ((string)$active['status'] !== 'pos_atendimento') {
            $internalStatus = !empty($active['answered_at']) ? 'atendida' : 'cancelada';
            db()->prepare("UPDATE calls SET status = 'cancelled', provider_status_raw = COALESCE(NULLIF(provider_status_raw, ''), status), internal_status = ?, ended_at = datetime('now'), updated_at = datetime('now') WHERE id = ?")
                ->execute([$internalStatus, $active['id']]);
            db()->prepare("UPDATE contacts SET status = 'concluido', reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE id = ?")
                ->execute([$active['contact_id']]);
            db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'call.operation_stopped', ?, 'cancelled', ?)")
                ->execute([$companyId, $active['id'], $active['status'], json_encode(['status' => $status], JSON_UNESCAPED_UNICODE)]);
        }
    }

    db()->prepare("UPDATE contacts SET status = 'novo', reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE company_id = ? AND reserved_by = ? AND status = 'reservado' AND attempts = 0")
        ->execute([$companyId, $agentId]);
}

function reserve_next_contact(int $campaignId, int $agentId, int $companyId): void
{
    $campaign = one('SELECT * FROM campaigns WHERE id = ? AND company_id = ? AND status = "Ativa"', [$campaignId, $companyId]);
    if (!$campaign) {
        flash('Campanha indisponivel.', 'error');
        return;
    }

    $contact = next_eligible_contact($campaign, $companyId);

    if (!$contact) {
        flash('Nao ha leads elegiveis nesta campanha.', 'error');
        return;
    }

    db()->prepare("UPDATE contacts SET reserved_by = ?, reserved_at = datetime('now'), reservation_expires_at = datetime('now', '+10 minutes'), status = 'reservado' WHERE id = ?")
        ->execute([$agentId, $contact['id']]);
    db()->prepare("UPDATE users SET status = 'Disponivel' WHERE id = ?")->execute([$agentId]);
    audit('reservou_contato', 'contacts:' . $contact['id']);
    flash('Lead reservado para ligacao.');
}

function start_call(int $campaignId, int $contactId, int $agentId, int $companyId): bool
{
    $contact = one('SELECT * FROM contacts WHERE id = ? AND company_id = ? AND reserved_by = ?', [$contactId, $companyId, $agentId]);
    $campaign = one('SELECT * FROM campaigns WHERE id = ? AND company_id = ?', [$campaignId, $companyId]);
    $agent = one('SELECT * FROM users WHERE id = ? AND company_id = ?', [$agentId, $companyId]);
    if (!$contact || !$campaign) {
        flash('Lead nao reservado para este consultor.', 'error');
        return false;
    }

    if (is_phone_blocked($companyId, (string)$contact['phone_e164'])) {
        db()->prepare("UPDATE contacts SET status = 'novo', reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE id = ?")
            ->execute([$contactId]);
        flash('Chamada nao iniciada: este numero esta na lista de bloqueio.', 'error');
        return false;
    }
    $telephony = telephony_call_allowed($companyId);
    if (!$telephony['ok']) {
        db()->prepare("UPDATE contacts SET status = 'novo', reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE id = ?")
            ->execute([$contactId]);
        flash((string)$telephony['message'], 'error');
        return false;
    }

    $asterisk = asterisk_config();
    if (!empty($asterisk['enabled']) && ($asterisk['active_mode'] ?? '') === 'ASTERISK') {
        $active = (int)scalar("SELECT COUNT(*) FROM calls WHERE company_id = ? AND telephony_mode = 'ASTERISK' AND status IN ('in_progress','calling_origin','ringing','answered')", [$companyId]);
        if ($active >= 1) {
            flash('O modo Asterisk permite somente uma chamada simultanea nesta etapa.', 'error');
            return false;
        }
    }    $providerCall = make_provider_call($campaign, $contact, $agent ?: []);
    if (!$providerCall['ok']) {
        db()->prepare("INSERT INTO call_events (company_id, event_name, old_status, new_status, payload) VALUES (?, 'call.provider_failed', 'reserved', 'failed', ?)")
            ->execute([$companyId, json_encode($providerCall['payload'], JSON_UNESCAPED_UNICODE)]);
        log_call_status($companyId, null, (string)($providerCall['provider'] ?? 'Nvoip'), (string)($providerCall['status'] ?? 'failed'), (string)($providerCall['message'] ?? 'provider_failed'), (array)($providerCall['payload'] ?? []));
        db()->prepare("UPDATE contacts SET status = 'novo', reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE id = ? AND attempts = 0")
            ->execute([$contactId]);
        flash($providerCall['message'], 'error');
        return false;
    }

    $answeredAt = call_was_answered(['status' => $providerCall['status']]) ? utc_now_storage() : null;
    $originNumber = (string)($providerCall['payload']['request']['bina'] ?? $providerCall['payload']['bina'] ?? $campaign['caller_id'] ?? '');
    $attemptNumber = max(1, (int)($contact['attempts'] ?? 0) + 1);
    $internalStatus = normalize_call_attempt_status((string)$providerCall['status'], ['event' => 'start']);
    $billingRateMicros = call_plan_rate_micros($companyId);
    db()->prepare("INSERT INTO calls (company_id, campaign_id, contact_id, agent_id, provider, external_call_id, provider_call_id, origin_number, destination_number, status, provider_status_raw, internal_status, attempt_number, billing_rate_micros, telephony_period_id, telephony_mode, telephony_trunk, provider_channel_id, provider_linked_id, provider_bridge_id, started_at, ringing_at, answered_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'), ?)")
        ->execute([$companyId, $campaignId, $contactId, $agentId, $providerCall['provider'], $providerCall['external_call_id'], $providerCall['external_call_id'], $originNumber, $contact['phone_e164'], $providerCall['status'], (string)$providerCall['status'], $internalStatus, $attemptNumber, $billingRateMicros, (int)$telephony['state']['period_id'], (string)($providerCall['telephony_mode'] ?? 'NVOIP_DIRECT'), (string)($providerCall['telephony_trunk'] ?? 'NVOIP_DIRECT'), (string)($providerCall['provider_channel_id'] ?? ''), (string)($providerCall['provider_linked_id'] ?? ''), (string)($providerCall['provider_bridge_id'] ?? ''), $answeredAt]);
    $callId = (int)db()->lastInsertId();
    db()->prepare("UPDATE contacts SET status = 'em_ligacao', attempts = attempts + 1, last_call_at = datetime('now') WHERE id = ?")->execute([$contactId]);
    $nextAgentStatus = (($agent['status'] ?? '') === 'Discando automatico') ? 'Discando automatico' : 'Em ligacao';
    db()->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$nextAgentStatus, $agentId]);
    db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'call.started', 'reserved', 'in_progress', ?)")
        ->execute([$companyId, $callId, json_encode($providerCall['payload'], JSON_UNESCAPED_UNICODE)]);
    log_call_status($companyId, $callId, (string)($providerCall['provider'] ?? 'Nvoip'), (string)$providerCall['status'], 'call_started', (array)$providerCall['payload']);
    audit('iniciou_ligacao', 'calls:' . $callId);
    flash($providerCall['message']);
    return true;
}

function get_or_create_manual_campaign(int $companyId, int $userId): array
{
    $campaign = one("
        SELECT c.*
        FROM campaigns c
        JOIN contact_lists l ON l.id = c.list_id
        WHERE c.company_id = ?
          AND c.name = 'Ligacao manual'
          AND c.dialer_type = 'manual'
          AND l.name = 'Ligacoes manuais'
        ORDER BY c.id DESC
        LIMIT 1
    ", [$companyId]);
    if ($campaign) {
        return $campaign;
    }

    $list = one("SELECT * FROM contact_lists WHERE company_id = ? AND name = 'Ligacoes manuais' ORDER BY id DESC LIMIT 1", [$companyId]);
    if (!$list) {
        db()->prepare("INSERT INTO contact_lists (company_id, name, description, source, status, created_by)
            VALUES (?, 'Ligacoes manuais', 'Lista interna para chamadas manuais sem campanha ativa.', 'Manual', 'Disponivel', ?)")
            ->execute([$companyId, $userId]);
        $listId = (int)db()->lastInsertId();
    } else {
        $listId = (int)$list['id'];
    }

    db()->prepare("INSERT INTO campaigns (company_id, list_id, name, description, dialer_type, caller_id, sip_trunk, script, max_attempts, recording_enabled, status)
        VALUES (?, ?, 'Ligacao manual', 'Campanha interna para chamadas feitas pelo webphone.', 'manual', '', 'Telefonia gerenciada', 'Chamada manual pelo webphone.', 1, 1, 'Manual')")
        ->execute([$companyId, $listId]);

    return one('SELECT * FROM campaigns WHERE id = ?', [(int)db()->lastInsertId()]) ?: [];
}

function start_manual_call(int $campaignId, string $phoneRaw, int $agentId, int $companyId): int
{
    $phone = normalize_phone($phoneRaw);
    if (!$phone) {
        flash('Telefone manual invalido.', 'error');
        return $campaignId;
    }
    if (is_phone_blocked($companyId, $phone)) {
        flash('Chamada manual bloqueada: este numero esta na lista de bloqueio.', 'error');
        return $campaignId;
    }

    $campaign = $campaignId ? one('SELECT * FROM campaigns WHERE id = ? AND company_id = ?', [$campaignId, $companyId]) : null;
    if (!$campaign) {
        $campaign = get_or_create_manual_campaign($companyId, $agentId);
        $campaignId = (int)($campaign['id'] ?? 0);
    }

    if (!$campaignId || !$campaign) {
        flash('Nao foi possivel preparar a ligacao manual.', 'error');
        return $campaignId;
    }

    $listId = (int)$campaign['list_id'];
    $contact = one("SELECT * FROM contacts WHERE company_id = ? AND list_id = ? AND phone_e164 = ? AND status <> 'excluido'", [$companyId, $listId, $phone]);
    if (!$contact) {
        db()->prepare("INSERT INTO contacts (company_id, list_id, name, phone_raw, phone_e164, origin, status, reserved_by, reserved_at, reservation_expires_at)
            VALUES (?, ?, 'Ligacao manual', ?, ?, 'Manual', 'reservado', ?, datetime('now'), datetime('now', '+10 minutes'))")
            ->execute([$companyId, $listId, $phoneRaw, $phone, $agentId]);
        $contactId = (int)db()->lastInsertId();
    } else {
        $contactId = (int)$contact['id'];
        db()->prepare("UPDATE contacts SET reserved_by = ?, reserved_at = datetime('now'), reservation_expires_at = datetime('now', '+10 minutes'), status = 'reservado' WHERE id = ?")
            ->execute([$agentId, $contactId]);
    }

    start_call($campaignId, $contactId, $agentId, $companyId);
    return $campaignId;
}

function finish_call(int $callId, int $resultId, string $notes, int $companyId): void
{
    $call = one('SELECT * FROM calls WHERE id = ? AND company_id = ?', [$callId, $companyId]);
    $result = one('SELECT * FROM call_results WHERE id = ? AND (company_id = ? OR company_id IS NULL)', [$resultId, $companyId]);
    if (!$call || !$result) {
        flash('Chamada ou resultado invalido.', 'error');
        return;
    }

    if ((string)($call['telephony_mode'] ?? '') === 'ASTERISK') {
        try { (new AsteriskProvider(asterisk_config()))->hangup($call); }
        catch (Throwable $e) { log_call_status($companyId, $callId, 'Asterisk ARI', 'hangup_failed', $e->getMessage()); }
    }
    $duration = call_conversation_duration_seconds($call, time());

    if ($action === 'adjust_telephony_credit') {
        if (!is_platform_admin($user)) {
            http_response_code(403);
            exit('Acesso negado.');
        }
        $amount = billing_input_to_micros((string)post('amount'));
        try {
            if ($amount === null || $amount <= 0) {
                throw new RuntimeException('Informe um valor de ajuste maior que zero.');
            }
            telephony_manual_adjustment((int)post('company_id'), (int)$user['id'], $amount, (string)post('entry_type'), trim((string)post('notes')));
            flash('Ajuste de credito de telefonia registrado.');
        } catch (Throwable $e) {
            flash('Nao foi possivel registrar o ajuste: ' . $e->getMessage(), 'error');
        }
        redirect('?page=costs');
    }
    $providerBillable = (int)($call['billable_seconds'] ?? 0) > 0 ? (int)$call['billable_seconds'] : null;
    $billable = call_billable_seconds($call, $duration, true, $providerBillable);
    $billing = call_billing_values($call, $billable);
    $recording = $call['recording_url'] ?: (str_contains(strtolower((string)$call['provider']), 'demo') ? demo_recording_url($callId) : null);
    db()->prepare("UPDATE calls SET status = 'completed', provider_status_raw = COALESCE(NULLIF(provider_status_raw, ''), status), internal_status = 'atendida', ended_at = datetime('now'), duration_seconds = ?, billable_seconds = ?, result_id = ?, notes = ?, recording_url = ?, billing_rate_micros = ?, estimated_cost_micros = ?, estimated_cost = ?, updated_at = datetime('now') WHERE id = ?")
        ->execute([$duration, $billable, $resultId, $notes, $recording, $billing['rate_micros'], $billing['cost_micros'], $billing['cost_decimal'], $callId]);
    telephony_record_call_debit($call, $billing, (int)($call['agent_id'] ?? 0) ?: null);
    db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'call.completed', 'in_progress', 'completed', ?)")
        ->execute([$companyId, $callId, json_encode(['duration_seconds' => $duration, 'billable_seconds' => $billable, 'estimated_cost' => $billing['cost_decimal']])]);

    $contactStatus = 'concluido';
    if ($result['action'] === 'retornar_fila') {
        $contactStatus = 'retentar';
    }
    if ($result['action'] === 'bloquear') {
        $contact = one('SELECT * FROM contacts WHERE id = ? AND company_id = ?', [$call['contact_id'], $companyId]);
        if ($contact && !is_test_phone_exception((string)($contact['phone_e164'] ?? ''))) {
            db()->prepare("INSERT OR IGNORE INTO blocklist (company_id, phone_e164, reason, source, responsible_user_id, notes) VALUES (?, ?, ?, 'resultado_chamada', ?, ?)")
                ->execute([$companyId, $contact['phone_e164'], $result['name'], $call['agent_id'], $notes]);
        }
        $contactStatus = 'bloqueado';
    }

    db()->prepare("UPDATE contacts SET status = ?, reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE id = ?")
        ->execute([$contactStatus, $call['contact_id']]);
    db()->prepare("UPDATE users SET status = 'Pos-atendimento' WHERE id = ?")->execute([$call['agent_id']]);
    audit('finalizou_ligacao', 'calls:' . $callId, null, ['result' => $result['name'], 'duration' => $duration]);
}

function update_answered_call(int $callId, int $resultId, string $notes, int $companyId, int $agentId): void
{
    $call = one('SELECT * FROM calls WHERE id = ? AND company_id = ? AND agent_id = ?', [$callId, $companyId, $agentId]);
    $result = one('SELECT * FROM call_results WHERE id = ? AND (company_id = ? OR company_id IS NULL)', [$resultId, $companyId]);
    if (!$call || !$result) {
        flash('Chamada ou resultado invalido para atualizar.', 'error');
        return;
    }

    db()->prepare("UPDATE calls SET result_id = ?, notes = ?, internal_status = CASE WHEN answered_at IS NOT NULL OR duration_seconds > 5 THEN 'atendida' ELSE COALESCE(internal_status, 'atendida') END, updated_at = datetime('now') WHERE id = ?")
        ->execute([$resultId, $notes, $callId]);

    $contactStatus = 'concluido';
    if ($result['action'] === 'retornar_fila') {
        $contactStatus = 'retentar';
    }
    if ($result['action'] === 'bloquear') {
        $contact = one('SELECT * FROM contacts WHERE id = ? AND company_id = ?', [$call['contact_id'], $companyId]);
        if ($contact && !is_test_phone_exception((string)($contact['phone_e164'] ?? ''))) {
            db()->prepare("INSERT OR IGNORE INTO blocklist (company_id, phone_e164, reason, source, responsible_user_id, notes) VALUES (?, ?, ?, 'resultado_chamada', ?, ?)")
                ->execute([$companyId, $contact['phone_e164'], $result['name'], $agentId, $notes]);
        }
        $contactStatus = 'bloqueado';
    }

    db()->prepare('UPDATE contacts SET status = ? WHERE id = ? AND company_id = ?')
        ->execute([$contactStatus, $call['contact_id'], $companyId]);
    db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'call.history_updated', ?, ?, ?)")
        ->execute([$companyId, $callId, $call['status'], $call['status'], json_encode(['result' => $result['name']], JSON_UNESCAPED_UNICODE)]);
    audit('atualizou_ligacao_atendida', 'calls:' . $callId, $call, ['result' => $result['name']]);
    flash('Dados da chamada atendida atualizados.');
}

function quick_hangup(int $callId, int $companyId): void
{
    $call = one("SELECT * FROM calls WHERE id = ? AND company_id = ? AND status IN ('in_progress','calling_origin','ringing','answered')", [$callId, $companyId]);
    if (!$call) {
        flash('Nenhuma chamada ativa para encerrar.', 'error');
        return;
    }
    if ((string)($call['telephony_mode'] ?? '') === 'ASTERISK') {
        try { (new AsteriskProvider(asterisk_config()))->hangup($call); }
        catch (Throwable $e) { log_call_status($companyId, $callId, 'Asterisk ARI', 'hangup_failed', $e->getMessage()); }
    }
    $duration = call_conversation_duration_seconds($call, time());
    $wasAnswered = !empty($call['answered_at']);
    $providerBillable = (int)($call['billable_seconds'] ?? 0) > 0 ? (int)$call['billable_seconds'] : null;
    $billable = call_billable_seconds($call, $duration, $wasAnswered, $providerBillable);
    $billing = call_billing_values($call, $billable);
    db()->prepare("UPDATE calls SET status = 'completed', provider_status_raw = COALESCE(NULLIF(provider_status_raw, ''), status), internal_status = CASE WHEN answered_at IS NOT NULL THEN 'atendida' ELSE 'cancelada' END, ended_at = datetime('now'), duration_seconds = ?, billable_seconds = ?, billing_rate_micros = ?, estimated_cost_micros = ?, estimated_cost = ?, updated_at = datetime('now') WHERE id = ?")
        ->execute([$duration, $billable, $billing['rate_micros'], $billing['cost_micros'], $billing['cost_decimal'], $callId]);
    telephony_record_call_debit($call, $billing, (int)($call['agent_id'] ?? 0) ?: null);
    db()->prepare("UPDATE contacts SET status = 'concluido', reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE id = ?")
        ->execute([$call['contact_id']]);
    db()->prepare("UPDATE users SET status = 'Pos-atendimento' WHERE id = ?")->execute([$call['agent_id']]);
    db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'call.quick_hangup', ?, 'completed', ?)")
        ->execute([$companyId, $callId, $call['status'], json_encode(['duration_seconds' => $duration, 'billable_seconds' => $billable, 'estimated_cost' => $billing['cost_decimal']])]);
    audit('encerrou_ligacao_webfone', 'calls:' . $callId);
    flash('Chamada encerrada pelo webfone.');
}

function handle_nvoip_webhook(): never
{
    $payload = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
    $companyId = webhook_company_id($payload);
    $recording = recording_url_from_payload($payload);
    $matchKey = webhook_match_key($payload);
    $call = find_call_from_webhook($payload, $companyId);
    if (!$call) {
        log_nvoip_webhook($companyId, null, $payload, 'call_not_found', $recording, $matchKey);
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'call_not_found']);
        exit;
    }

    $expected = (string)nvoip_config((int)$call['company_id'])['webhook_secret'];
    $received = $_SERVER['HTTP_X_NVOIP_SECRET'] ?? $_GET['secret'] ?? '';
    if ($expected !== '' && !hash_equals($expected, (string)$received)) {
        log_nvoip_webhook((int)$call['company_id'], (int)$call['id'], $payload, 'unauthorized', $recording, $matchKey);
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'unauthorized']);
        exit;
    }

    $rawStatus = (string)(first_payload_value($payload, ['status', 'call_status', 'state']) ?? $call['status']);
    $status = normalize_call_status($rawStatus);
    $reportedDuration = max(0, (int)($payload['duration_seconds'] ?? $payload['duration'] ?? $call['duration_seconds']));
    $recording = $recording ?: (string)($call['recording_url'] ?? '');

    $finalStatuses = ['completed', 'failed', 'cancelled', 'busy', 'no_answer', 'missed'];
    $answeredAtSql = $status === 'answered' ? "COALESCE(answered_at, datetime('now'))" : 'answered_at';
    $endedAtSql = in_array($status, $finalStatuses, true) ? "COALESCE(ended_at, datetime('now'))" : 'ended_at';
    $internalStatus = normalize_call_attempt_status($rawStatus, [
        'event' => in_array($status, ['answered', 'connected'], true) ? 'answered' : '',
        'answered_at' => $status === 'answered' ? utc_now_storage() : ($call['answered_at'] ?? null),
        'duration_seconds' => $reportedDuration,
        'cause' => (string)($payload['cause'] ?? ''),
        'reason' => (string)($payload['reason'] ?? ''),
        'stopped_by_user' => !empty($payload['stopped_by_user']),
    ]);
    $providerBillable = first_payload_value($payload, ['billsec', 'billable_seconds', 'billable_duration', 'charged_seconds', 'talk_time']);
    $wasAnswered = $internalStatus === 'atendida' || !empty($call['answered_at']) || in_array($status, ['answered', 'connected'], true);
    $duration = $wasAnswered ? call_conversation_duration_seconds($call, time()) : 0;
    if ($duration === 0 && $wasAnswered && $providerBillable !== null && $providerBillable !== '' && is_numeric($providerBillable)) {
        $duration = max(0, (int)$providerBillable);
    }
    $billable = call_billable_seconds($call, $duration, $wasAnswered, $providerBillable);
    $billing = call_billing_values($call, $billable);
    db()->prepare("UPDATE calls SET status = ?, provider_call_id = COALESCE(NULLIF(provider_call_id, ''), NULLIF(external_call_id, '')), provider_status_raw = ?, internal_status = ?, duration_seconds = ?, billable_seconds = ?, billing_rate_micros = ?, estimated_cost_micros = ?, estimated_cost = ?, recording_url = NULLIF(?, ''), answered_at = {$answeredAtSql}, ended_at = {$endedAtSql}, updated_at = datetime('now') WHERE id = ?")
        ->execute([$status, $rawStatus, $internalStatus, $duration, $billable, $billing['rate_micros'], $billing['cost_micros'], $billing['cost_decimal'], $recording, $call['id']]);
    if (in_array($status, $finalStatuses, true)) {
        telephony_record_call_debit($call, $billing, (int)($call['agent_id'] ?? 0) ?: null);
    }
    db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'nvoip.webhook', ?, ?, ?)")
        ->execute([$call['company_id'], $call['id'], $call['status'], $status, json_encode($payload, JSON_UNESCAPED_UNICODE)]);
    log_call_status((int)$call['company_id'], (int)$call['id'], 'Nvoip', $status, 'webhook', $payload);
    log_nvoip_webhook((int)$call['company_id'], (int)$call['id'], $payload, $status, $recording, $matchKey);

    echo json_encode(['ok' => true]);
    exit;
}

function save_callback_for_call(array $call, int $agentId, string $scheduledAt, string $priority, string $reason, string $notes): void
{
    $scheduledAt = callback_datetime_storage($scheduledAt);
    $callId = (int)($call['id'] ?? 0);
    $companyId = (int)($call['company_id'] ?? 0);
    if ($callId <= 0 || $companyId <= 0 || trim($scheduledAt) === '') {
        return;
    }
    $existing = one('SELECT id FROM callbacks WHERE company_id = ? AND call_id = ? ORDER BY id DESC LIMIT 1', [$companyId, $callId]);
    if ($existing) {
        db()->prepare("UPDATE callbacks SET campaign_id = ?, contact_id = ?, agent_id = ?, scheduled_at = ?, priority = ?, reason = ?, notes = ?, status = 'pendente' WHERE id = ?")
            ->execute([$call['campaign_id'] ?: null, $call['contact_id'], $agentId, $scheduledAt, $priority ?: 'normal', $reason, $notes, (int)$existing['id']]);
        return;
    }
    db()->prepare("INSERT INTO callbacks (company_id, call_id, campaign_id, contact_id, agent_id, scheduled_at, priority, reason, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$companyId, $callId, $call['campaign_id'] ?: null, $call['contact_id'], $agentId, $scheduledAt, $priority ?: 'normal', $reason, $notes]);
}

function handle_recording_file(): never
{
    require_login();
    $user = current_user();
    if (!can('recordings') && !can('supervisor')) {
        http_response_code(403);
        echo 'Acesso negado.';
        exit;
    }

    $callId = (int)($_GET['id'] ?? 0);
    [$clause, $params] = scoped_calls_clause('co', $user);
    $params[] = $callId;
    $call = one("SELECT co.*, ct.name contato FROM calls co LEFT JOIN contacts ct ON ct.id = co.contact_id WHERE {$clause} AND co.id = ? LIMIT 1", $params);
    if (!$call || empty($call['recording_url'])) {
        http_response_code(404);
        echo 'Gravacao nao encontrada.';
        exit;
    }

    $url = (string)$call['recording_url'];
    if (!preg_match('~^https?://~i', $url)) {
        http_response_code(404);
        echo 'Gravacao ainda nao disponivel.';
        exit;
    }

    $config = nvoip_config((int)$call['company_id']);
    $headers = ['Accept: audio/*,*/*'];
    if (!empty($config['napikey'])) {
        $headers[] = 'napikey: ' . $config['napikey'];
        $headers[] = 'Napikey: ' . $config['napikey'];
    } elseif (!empty($config['user_token'])) {
        $headers[] = 'Authorization: Bearer ' . $config['user_token'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = (string)(curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'audio/mpeg');
    curl_close($ch);

    if ($body === false || $status >= 400) {
        http_response_code(502);
        echo 'Nao foi possivel carregar a gravacao na origem.';
        exit;
    }

    $download = ($_GET['download'] ?? '') === '1';
    $safeName = preg_replace('/[^a-z0-9_-]+/i', '-', (string)($call['contato'] ?: 'ligacao'));
    header('Content-Type: ' . ($contentType ?: 'audio/mpeg'));
    header('Content-Length: ' . strlen((string)$body));
    if ($download) {
        header('Content-Disposition: attachment; filename="ligflow-gravacao-' . $callId . '-' . trim((string)$safeName, '-') . '.mp3"');
    } else {
        header('Content-Disposition: inline');
    }
    echo $body;
    exit;
}

function is_secure_or_local_request(): bool
{
    $https = strtolower((string)($_SERVER['HTTPS'] ?? '')) === 'on';
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    return $https || str_starts_with($host, 'localhost') || str_starts_with($host, '127.0.0.1');
}

function asterisk_diagnostics_can_access(?array $user = null): bool
{
    $user ??= current_user();
    return $user !== null && (is_platform_admin($user) || can('asterisk_diagnostics'));
}

function asterisk_diagnostics_company_id(array $user): int
{
    $companyId = (int)($user['company_id'] ?? 0);
    if (is_platform_admin($user) && isset($_GET['company_id']) && (int)$_GET['company_id'] > 0) {
        $candidate = (int)$_GET['company_id'];
        if (one('SELECT id FROM companies WHERE id = ?', [$candidate])) {
            $companyId = $candidate;
        }
    }
    return $companyId;
}

function asterisk_diagnostics_mask_phone(?string $value): string
{
    $digits = preg_replace('/\D+/', '', (string)$value);
    if ($digits === '') return '-';
    return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
}

function asterisk_diagnostics_duration(int $seconds): string
{
    $seconds = max(0, $seconds);
    return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
}

function asterisk_diagnostics_filters(): array
{
    $status = strtoupper(trim((string)($_GET['status'] ?? '')));
    $allowed = ['ORIGINATING', 'RINGING', 'WINNER', 'CONNECTED', 'NO_WINNER', 'CANCELLED'];
    return [
        'campaign_id' => max(0, (int)($_GET['campaign_id'] ?? 0)),
        'agent_id' => max(0, (int)($_GET['agent_id'] ?? 0)),
        'batch_id' => max(0, (int)($_GET['batch_id'] ?? 0)),
        'status' => in_array($status, $allowed, true) ? $status : '',
        'from' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['from'] ?? '')) ? (string)$_GET['from'] : '',
        'to' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['to'] ?? '')) ? (string)$_GET['to'] : '',
        'page' => max(1, (int)($_GET['batch_page'] ?? 1)),
    ];
}

function asterisk_diagnostics_is_stale(?string $value, int $seconds): bool
{
    if (!$value) return true;
    try {
        $at = new DateTimeImmutable((string)$value, new DateTimeZone('UTC'));
        return time() - $at->getTimestamp() > $seconds;
    } catch (Throwable) {
        return true;
    }
}

function asterisk_diagnostics_payload(int $companyId, array $filters): array
{
    $where = ['b.company_id = ?'];
    $params = [$companyId];
    foreach (['campaign_id' => 'campaign_id', 'agent_id' => 'agent_id', 'batch_id' => 'id'] as $filter => $column) {
        if (!empty($filters[$filter])) {
            $where[] = 'b.' . $column . ' = ?';
            $params[] = (int)$filters[$filter];
        }
    }
    if ($filters['status'] !== '') { $where[] = 'b.status = ?'; $params[] = $filters['status']; }
    if ($filters['from'] !== '') { $where[] = 'b.created_at >= ?'; $params[] = $filters['from'] . ' 00:00:00'; }
    if ($filters['to'] !== '') { $where[] = "b.created_at < datetime(?, '+1 day')"; $params[] = $filters['to'] . ' 00:00:00'; }
    $whereSql = implode(' AND ', $where);
    $perPage = 10;
    $offset = ($filters['page'] - 1) * $perPage;
    $total = (int)scalar('SELECT COUNT(*) FROM dial_batches b WHERE ' . $whereSql, $params);
    $sql = "SELECT b.id, b.campaign_id, b.agent_id, b.requested_parallelism, b.effective_parallelism, b.telephony_mode, b.telephony_trunk, b.status, b.winner_call_id, b.next_started_at, b.created_at, b.updated_at, co.trade_name AS tenant_name, cp.name AS campaign_name, u.name AS agent_name,
        COUNT(ca.id) AS originated_count,
        SUM(CASE WHEN ca.status IN ('in_progress','calling_origin','ringing','answered','connected') AND ca.finalized_at IS NULL THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN ca.finalized_at IS NOT NULL OR ca.status IN ('completed','failed','cancelled','busy','no_answer') THEN 1 ELSE 0 END) AS finalized_count,
        SUM(CASE WHEN ca.race_outcome = 'WINNER' THEN 1 ELSE 0 END) AS winner_count,
        SUM(CASE WHEN ca.race_outcome = 'LOSER' THEN 1 ELSE 0 END) AS loser_count,
        SUM(CASE WHEN ca.race_outcome = 'LATE_ANSWERED' THEN 1 ELSE 0 END) AS late_answered_count,
        SUM(CASE WHEN ca.race_outcome IN ('LOSER','LATE_ANSWERED') AND ca.finalized_at IS NULL AND ca.status IN ('in_progress','calling_origin','ringing','answered','connected') THEN 1 ELSE 0 END) AS active_loser_count,
        MAX(COALESCE(ca.last_event_at, ca.updated_at, ca.created_at)) AS last_call_event_at,
        wc.provider_bridge_id AS winner_bridge_id
        FROM dial_batches b
        JOIN companies co ON co.id = b.company_id
        LEFT JOIN campaigns cp ON cp.id = b.campaign_id AND cp.company_id = b.company_id
        LEFT JOIN users u ON u.id = b.agent_id AND u.company_id = b.company_id
        LEFT JOIN calls ca ON ca.dial_batch_id = b.id AND ca.company_id = b.company_id
        LEFT JOIN calls wc ON wc.id = b.winner_call_id AND wc.company_id = b.company_id
        WHERE {$whereSql}
        GROUP BY b.id
        ORDER BY b.id DESC LIMIT {$perPage} OFFSET {$offset}";
    $batches = rows($sql, $params);
    $activeStatuses = "'ORIGINATING','RINGING','WINNER','CONNECTED'";
    $duplicateAgents = rows("SELECT agent_id, COUNT(*) total FROM dial_batches WHERE company_id = ? AND status IN ({$activeStatuses}) GROUP BY agent_id HAVING COUNT(*) > 1", [$companyId]);
    $duplicateAgentIds = array_flip(array_map(static fn(array $row): int => (int)$row['agent_id'], $duplicateAgents));
    $lastWorkerEvent = one("SELECT MAX(e.created_at) AS created_at FROM asterisk_ari_events e JOIN calls ca ON ca.id = e.call_id WHERE ca.company_id = ?", [$companyId]);
    $workerAt = (string)($lastWorkerEvent['created_at'] ?? '');
    $activeCount = (int)scalar("SELECT COUNT(*) FROM dial_batches WHERE company_id = ? AND status IN ({$activeStatuses})", [$companyId]);
    $config = asterisk_config();
    $alerts = [];
    if ($activeCount > 0 && asterisk_diagnostics_is_stale($workerAt ?: null, 120)) {
        $alerts[] = ['level' => 'warning', 'message' => 'Worker ARI sem evento recente para lote ativo.'];
    }
    foreach ($batches as &$batch) {
        $batch['originated_count'] = (int)$batch['originated_count'];
        $batch['active_count'] = (int)$batch['active_count'];
        $batch['finalized_count'] = (int)$batch['finalized_count'];
        $batch['winner_count'] = (int)$batch['winner_count'];
        $batch['loser_count'] = (int)$batch['loser_count'];
        $batch['late_answered_count'] = (int)$batch['late_answered_count'];
        $batch['active_loser_count'] = (int)$batch['active_loser_count'];
        if ((int)$batch['effective_parallelism'] < (int)$batch['requested_parallelism']) $alerts[] = ['level' => 'warning', 'batch_id' => (int)$batch['id'], 'message' => 'Paralelismo efetivo menor que o solicitado.'];
        if (isset($duplicateAgentIds[(int)$batch['agent_id']])) $alerts[] = ['level' => 'error', 'batch_id' => (int)$batch['id'], 'message' => 'Mais de um lote ativo para este consultor.'];
        if ((int)$batch['winner_call_id'] > 0 && trim((string)$batch['winner_bridge_id']) === '') $alerts[] = ['level' => 'error', 'batch_id' => (int)$batch['id'], 'message' => 'Vencedora sem bridge conhecida.'];
        if ((int)$batch['active_loser_count'] > 0) $alerts[] = ['level' => 'error', 'batch_id' => (int)$batch['id'], 'message' => 'Perdedora ainda ativa apos a eleicao.'];
        if (in_array((string)$batch['status'], ['ORIGINATING', 'RINGING', 'WINNER', 'CONNECTED'], true) && asterisk_diagnostics_is_stale((string)($batch['last_call_event_at'] ?: $batch['updated_at']), 90)) $alerts[] = ['level' => 'warning', 'batch_id' => (int)$batch['id'], 'message' => 'Lote ativo sem atualizacao recente.'];
    }
    unset($batch);
    return [
        'health' => [
            'ari' => ['configured' => $config['ari_url'] !== '' && $config['ari_username'] !== '' && $config['ari_password'] !== '', 'last_event_at' => $workerAt ?: null, 'state' => $workerAt !== '' && !asterisk_diagnostics_is_stale($workerAt, 120) ? 'Atividade recente' : 'Sem evento recente'],
            'worker' => ['last_event_at' => $workerAt ?: null, 'state' => $activeCount === 0 ? 'Sem lote ativo' : (asterisk_diagnostics_is_stale($workerAt ?: null, 120) ? 'Sem atividade recente' : 'Ativo')],
            'webrtc' => ['endpoint' => (string)$config['consultant_endpoint'], 'state' => $config['sip_wss_url'] !== '' && $config['sip_domain'] !== '' ? 'Configurado' : 'Configuracao incompleta'],
        ],
        'batches' => $batches,
        'alerts' => $alerts,
        'pagination' => ['page' => $filters['page'], 'per_page' => $perPage, 'total' => $total, 'pages' => max(1, (int)ceil($total / $perPage))],
    ];
}

function asterisk_diagnostics_batch_calls(int $companyId, int $batchId, int $page = 1): array
{
    $perPage = 10; $page = max(1, $page); $offset = ($page - 1) * $perPage;
    $batch = one('SELECT id FROM dial_batches WHERE id = ? AND company_id = ?', [$batchId, $companyId]);
    if (!$batch) return ['calls' => [], 'pagination' => ['page' => 1, 'pages' => 1, 'total' => 0]];
    $total = (int)scalar('SELECT COUNT(*) FROM calls WHERE company_id = ? AND dial_batch_id = ?', [$companyId, $batchId]);
    $calls = rows("SELECT ca.id, ca.agent_id, ca.provider_channel_id, ca.provider_linked_id, ca.provider_bridge_id, ca.status, ca.internal_status, ca.race_outcome, ca.started_at, ca.ringing_at, ca.answered_at, ca.connected_at, ca.ended_at, ca.finalized_at, ca.duration_seconds, ca.billable_seconds, ca.destination_number, ct.name AS lead_name, u.name AS resolved_agent_name, axe.extension AS asterisk_extension
        FROM calls ca
        LEFT JOIN contacts ct ON ct.id = ca.contact_id AND ct.company_id = ca.company_id
        LEFT JOIN users u ON u.id = ca.agent_id AND u.company_id = ca.company_id
        LEFT JOIN asterisk_user_extensions axe ON axe.company_id = ca.company_id AND axe.user_id = ca.agent_id AND axe.asterisk_server_id = 1 AND axe.status = 'Ativo'
        WHERE ca.company_id = ? AND ca.dial_batch_id = ? ORDER BY ca.id ASC LIMIT {$perPage} OFFSET {$offset}", [$companyId, $batchId]);
    foreach ($calls as &$call) {
        $call['phone_masked'] = asterisk_diagnostics_mask_phone((string)$call['destination_number']);
        unset($call['destination_number']);
        $call['hangup_requested'] = in_array((string)$call['race_outcome'], ['LOSER', 'LATE_ANSWERED', 'CANCELLED'], true);
        $call['hangup_confirmed'] = !empty($call['ended_at']) || !empty($call['finalized_at']);
    }
    unset($call);
    return ['calls' => $calls, 'pagination' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'pages' => max(1, (int)ceil($total / $perPage))]];
}

function handle_asterisk_diagnostics_data(): never
{
    require_login();
    header('Content-Type: application/json; charset=utf-8');
    $user = current_user();
    if (!asterisk_diagnostics_can_access($user)) {
        http_response_code(403);
        echo json_encode_safe(['ok' => false, 'error' => 'Sem permissao para diagnostico Asterisk.']);
        exit;
    }
    $companyId = asterisk_diagnostics_company_id($user);
    $filters = asterisk_diagnostics_filters();
    $payload = asterisk_diagnostics_payload($companyId, $filters);
    if ($filters['batch_id'] > 0) $payload['detail'] = asterisk_diagnostics_batch_calls($companyId, $filters['batch_id'], max(1, (int)($_GET['call_page'] ?? 1)));
    echo json_encode_safe(['ok' => true, 'company_id' => $companyId] + $payload);
    exit;
}
function handle_sip_config(): never
{
    require_login();
    $user = current_user();
    if (!$user || !can('agent')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Sem permissao para webphone SIP.']);
        exit;
    }
    if (!is_secure_or_local_request()) {
        http_response_code(426);
        echo json_encode(['ok' => false, 'error' => 'Use HTTPS para registrar o webphone SIP/WebRTC. Em desenvolvimento, localhost e permitido.']);
        exit;
    }

    $asterisk = asterisk_config();
    if ($asterisk['active_mode'] === 'ASTERISK') {
        if (asterisk_uses_local_ari($asterisk)) {
            $sipUsername = preg_replace('/^PJSIP\//i', '', (string)$asterisk['consultant_endpoint']);
            $sipPassword = trim((string)$asterisk['webrtc_password']);
            if ($sipPassword === '') {
                $sipPassword = trim((string)env_value('ASTERISK_WEBRTC_PASSWORD'));
            }
        } else {
            $extension = asterisk_user_extension_record((int)$user['company_id'], (int)$user['id']);
            $sipUsername = trim((string)($extension['extension'] ?? ''));
            $sipPassword = $extension && !empty($extension['sip_password_encrypted'])
                ? trim(decrypt_secret((string)$extension['sip_password_encrypted']))
                : '';
            if (!asterisk_user_extension_ready($extension)) {
                http_response_code(409);
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-Control: no-store, private');
                $extensionLabel = $sipUsername !== '' ? ' ' . $sipUsername : '';
                echo json_encode(['ok' => false, 'error' => 'Ramal Asterisk' . $extensionLabel . ' ainda nao foi provisionado. Aguarde o provisionamento antes de conectar o webphone.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        $isComplete = $asterisk['enabled']
            && valid_asterisk_webrtc_wss_url((string)$asterisk['sip_wss_url'])
            && valid_asterisk_webrtc_domain((string)$asterisk['sip_domain'])
            && valid_asterisk_webrtc_endpoint($sipUsername)
            && valid_asterisk_trunk_identifier((string)$asterisk['webrtc_context'])
            && $sipUsername !== ''
            && $sipPassword !== '';
        if (!$isComplete) {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, private');
            echo json_encode(['ok' => false, 'error' => 'Configuracao SIP/WebRTC do Asterisk incompleta. Revise WSS, dominio, endpoint, contexto e senha WebRTC.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        audit('consultou_config_sip', 'users:' . $user['id'], null, ['provider' => 'ASTERISK', 'has_sip_user' => true, 'has_sip_password' => true]);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        echo json_encode([
            'ok' => true,
            'wssUrl' => $asterisk['sip_wss_url'],
            'domain' => $asterisk['sip_domain'],
            'sipUsername' => $sipUsername,
            'sipPassword' => $sipPassword,
            'provider' => 'ASTERISK',
            'providerLabel' => 'Asterisk WebRTC',
            'autoAnswer' => false,
            'callbackTimeoutSeconds' => max(10, (int)$asterisk['originate_timeout_seconds']),
            'secureContext' => is_secure_or_local_request(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $config = nvoip_config((int)$user['company_id']);
    $sipUsername = (string)($config['user_sip'] ?: $user['extension'] ?: $config['numbersip']);
    $sipPassword = (string)$config['sip_password'];
    audit('consultou_config_sip', 'users:' . $user['id'], null, ['has_sip_user' => $sipUsername !== '', 'has_sip_password' => $sipPassword !== '']);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'wssUrl' => $config['sip_wss_url'] ?: 'wss://app.nvoip.com.br:7443',
        'domain' => $config['sip_domain'] ?: 'app.nvoip.com.br',
        'sipUsername' => $sipUsername,
        'sipPassword' => $sipPassword,
        'autoAnswer' => (int)$config['auto_answer_nvoip_callback'] === 1,
        'callbackTimeoutSeconds' => max(10, (int)$config['sip_callback_timeout_seconds']),
        'secureContext' => is_secure_or_local_request(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function handle_phone_history(): never
{
    require_login();
    $user = current_user();
    header('Content-Type: application/json; charset=utf-8');
    if (!$user || !can('agent')) {
        http_response_code(403);
        echo json_encode_safe(['ok' => false, 'error' => 'Sem permissao para consultar chamadas.']);
        exit;
    }

    $history = recent_phone_history((int)$user['company_id'], (int)$user['id']);
    $prepareItems = static function (array $items): array {
        return array_map(static function (array $call): array {
            $duration = (int)($call['duration_seconds'] ?? 0);
            return [
                'id' => (int)$call['id'],
                'phone' => utf8_text($call['destination_number'] ?? ''),
                'contact' => utf8_text($call['contato'] ?: 'Contato'),
                'location' => utf8_text($call['city'] ?: $call['state'] ?: 'Contato'),
                'time' => datetime_utc_display((string)($call['created_at'] ?? ''), 'H:i:s'),
                'duration' => $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '',
                'status' => utf8_text($call['status'] ?? ''),
                'result' => utf8_text($call['resultado'] ?? ''),
            ];
        }, $items);
    };

    echo json_encode_safe([
        'ok' => true,
        'todas' => $prepareItems($history['todas']),
        'recebidas' => $prepareItems($history['recebidas']),
        'realizadas' => $prepareItems($history['realizadas']),
        'perdidas' => $prepareItems($history['perdidas']),
    ]);
    exit;
}

function handle_callback_notifications(): never
{
    require_login();
    header('Content-Type: application/json; charset=utf-8');
    $user = current_user();
    if (!$user || !can('agent')) {
        http_response_code(403);
        echo json_encode_safe(['ok' => false, 'error' => 'Sem permissao para consultar retornos.']);
        exit;
    }

    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $windowStart = $now->modify('-1 day');
    $callbacks = rows("
        SELECT cb.id, cb.scheduled_at, cb.priority, cb.notes,
               COALESCE(ct.name, 'Contato') AS contact_name, ct.phone_e164
        FROM callbacks cb
        JOIN contacts ct ON ct.id = cb.contact_id AND ct.company_id = cb.company_id
        WHERE cb.company_id = ?
          AND cb.agent_id = ?
          AND cb.status = 'pendente'
          AND cb.scheduled_at <= ?
          AND cb.scheduled_at >= ?
        ORDER BY cb.scheduled_at ASC, cb.id ASC
        LIMIT 10
    ", [(int)$user['company_id'], (int)$user['id'], $now->format('Y-m-d H:i:s'), $windowStart->format('Y-m-d H:i:s')]);

    echo json_encode_safe(['ok' => true, 'callbacks' => array_map(static function (array $callback): array {
        return [
            'id' => (int)$callback['id'],
            'contact' => utf8_text($callback['contact_name'] ?? 'Contato'),
            'phone' => utf8_text($callback['phone_e164'] ?? ''),
            'scheduled_at' => utf8_text($callback['scheduled_at'] ?? ''),
            'priority' => utf8_text($callback['priority'] ?? 'normal'),
            'notes' => utf8_text($callback['notes'] ?? ''),
        ];
    }, $callbacks)]);
    exit;
}

function list_contacts_batch(int $listId, int $companyId, int $offset, int $limit, array $statusFilters = []): array
{
    $scanLimit = max($limit + 1, 11);
    $baseSql = "
        SELECT c.id, c.list_id, c.name, c.phone_raw, c.phone_e164, c.email, c.city, c.state, c.product, c.origin, c.notes, c.custom_json, c.status, c.attempts, c.last_call_at,
               lc.id AS last_call_id, lc.status AS call_status, lc.provider_status_raw, lc.answered_at, lc.ended_at,
               COALESCE(cr.name, '') AS result_name, COALESCE(cr.action, '') AS result_action
        FROM contacts c
        LEFT JOIN calls lc ON lc.id = (
            SELECT id FROM calls WHERE company_id = c.company_id AND contact_id = c.id ORDER BY id DESC LIMIT 1
        )
        LEFT JOIN call_results cr ON cr.id = lc.result_id
        WHERE c.list_id = ? AND c.company_id = ? AND c.status <> 'excluido'
        ORDER BY c.id DESC
    ";

    if ($statusFilters) {
        $contacts = hydrate_reprocess_lead_statuses(rows($baseSql, [$listId, $companyId]));
        $contacts = array_values(array_filter($contacts, static fn($contact) => in_array((string)$contact['reprocess_bucket'], $statusFilters, true)));
        $filteredTotal = count($contacts);
        $contacts = array_slice($contacts, max(0, $offset), $limit);
        return [
            'contacts' => $contacts,
            'has_more' => $offset + $limit < $filteredTotal,
            'next_offset' => $offset + $limit,
        ];
    }

    $contacts = rows($baseSql . ' LIMIT ? OFFSET ?', [$listId, $companyId, $scanLimit, max(0, $offset)]);

    $hasMore = count($contacts) > $limit;
    $contacts = array_slice($contacts, 0, $limit);
    $contacts = hydrate_reprocess_lead_statuses($contacts);
    return ['contacts' => $contacts, 'has_more' => $hasMore, 'next_offset' => $offset + $limit];
}

function handle_list_contacts_batch(): never
{
    require_login();
    header('Content-Type: application/json; charset=utf-8');
    if (!can('lists')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Sem permissao para consultar a lista.']);
        exit;
    }
    $user = current_user();
    $listId = max(0, (int)($_GET['list_id'] ?? 0));
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    $companyId = (int)($user['company_id'] ?? 0);
    $list = is_platform_admin()
        ? one('SELECT id, company_id FROM contact_lists WHERE id = ?', [$listId])
        : one('SELECT id, company_id FROM contact_lists WHERE id = ? AND company_id = ?', [$listId, $companyId]);
    if (!$list) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Lista nao encontrada.']);
        exit;
    }
    $allowed = array_values(array_diff(array_keys(lead_reprocess_status_labels()), ['all']));
    $rawStatuses = is_array($_GET['lead_statuses'] ?? null) ? $_GET['lead_statuses'] : [];
    $statuses = array_values(array_filter(array_map(static fn($value) => strtolower(trim((string)$value)), $rawStatuses), static fn($value) => in_array($value, $allowed, true)));
    $response = ['ok' => true] + list_contacts_batch($listId, (int)$list['company_id'], $offset, 10, $statuses);
    $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        http_response_code(500);
        $json = json_encode(['ok' => false, 'error' => 'Nao foi possivel preparar este lote de contatos.']);
    }
    echo $json;
    exit;
}

function mercado_pago_webhook_signature_valid(string $paymentId): bool
{
    $config = mercado_pago_config();
    $secret = (string)$config['webhook_secret'];
    if ($secret === '') return false;
    $signature = (string)($_SERVER['HTTP_X_SIGNATURE'] ?? '');
    $requestId = (string)($_SERVER['HTTP_X_REQUEST_ID'] ?? '');
    $parts = [];
    foreach (explode(',', $signature) as $part) {
        [$key,$value] = array_pad(explode('=', trim($part), 2), 2, '');
        $parts[$key] = $value;
    }
    $ts = (string)($parts['ts'] ?? '');
    $v1 = (string)($parts['v1'] ?? '');
    if ($ts === '' || $v1 === '' || $requestId === '') return false;
    $manifest = 'id:'.strtolower($paymentId).';request-id:'.$requestId.';ts:'.$ts.';';
    return hash_equals(hash_hmac('sha256', $manifest, $secret), $v1);
}

function handle_mercado_pago_webhook(): never
{
    header('Content-Type: application/json; charset=utf-8');
    $payload = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
    $paymentId = (string)($_GET['data_id'] ?? $_GET['id'] ?? $payload['data']['id'] ?? '');
    if ($paymentId === '' || !mercado_pago_webhook_signature_valid($paymentId)) {
        http_response_code(401); echo json_encode(['ok'=>false]); exit;
    }
    try { sync_mercado_pago_payment($paymentId); echo json_encode(['ok'=>true]); }
    catch (Throwable $e) { http_response_code(500); echo json_encode(['ok'=>false]); }
    exit;
}

function handle_payment_status(): never
{
    require_login();
    header('Content-Type: application/json; charset=utf-8');
    $user = current_user();
    $payment = one('SELECT id,status,provider_payment_id,provider_status,approved_at,expires_at,updated_at FROM payments WHERE id=? AND company_id=?', [(int)($_GET['id'] ?? 0),(int)$user['company_id']]);
    if (!$payment) { http_response_code(404); echo json_encode(['ok'=>false]); exit; }
    if (!empty($payment['provider_payment_id']) && in_array($payment['status'], ['CREATED','PENDING','IN_PROCESS'], true)) {
        try { sync_mercado_pago_payment((string)$payment['provider_payment_id']); $payment = one('SELECT id,status,provider_payment_id,provider_status,approved_at,expires_at,updated_at FROM payments WHERE id=?', [$payment['id']]); }
        catch (Throwable $e) { error_log('Falha ao sincronizar pagamento Mercado Pago: ' . $e->getMessage()); }
    }
    if ((string)($payment['status'] ?? '') === 'APPROVED') {
        try {
            apply_approved_payment((int)$payment['id'], ['date_approved' => $payment['approved_at'] ?? 'now']);
            $payment = one('SELECT id,status,provider_payment_id,provider_status,approved_at,expires_at,updated_at FROM payments WHERE id=?', [$payment['id']]);
        } catch (Throwable $e) {
            error_log('Falha ao aplicar renovacao aprovada: ' . $e->getMessage());
        }
    }
    if ((string)($payment['status'] ?? '') === 'APPROVED') {
        flash('Pagamento aprovado. Seu plano foi liberado com sucesso.', 'ok');
    }
    echo json_encode(['ok'=>true,'payment'=>$payment,'billing'=>tenant_billing_state((int)$user['company_id'])], JSON_UNESCAPED_UNICODE);
    exit;
}

function handle_sip_call_event(): never
{
    require_login();
    $user = current_user();
    header('Content-Type: application/json; charset=utf-8');
    if (!$user || !can('agent')) {
        http_response_code(403);
        echo json_encode_safe(['ok' => false, 'error' => 'Sem permissao para registrar chamada SIP.']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
    $event = (string)($payload['event'] ?? '');
    $companyId = (int)$user['company_id'];
    $agentId = (int)$user['id'];

    if ($event === 'start') {
        $phone = normalize_phone((string)($payload['phone'] ?? ''));
        if (!$phone) {
            http_response_code(422);
            echo json_encode_safe(['ok' => false, 'error' => 'Telefone invalido.']);
            exit;
        }
        if (is_phone_blocked($companyId, $phone)) {
            http_response_code(409);
            echo json_encode_safe(['ok' => false, 'error' => 'Chamada nao iniciada: este numero esta na lista de bloqueio.']);
            exit;
        }
        $telephony = telephony_call_allowed($companyId);
        if (!$telephony['ok']) {
            http_response_code(402);
            echo json_encode_safe(['ok' => false, 'error' => (string)$telephony['message']]);
            exit;
        }

        $campaignId = (int)($payload['campaign_id'] ?? 0);
        $autoDialing = !empty($payload['auto_dialing'])
            || (string)($user['status'] ?? '') === 'Discando automatico';
        $campaign = $campaignId ? one('SELECT * FROM campaigns WHERE id = ? AND company_id = ?', [$campaignId, $companyId]) : null;
        $contact = null;
        if ($campaign) {
            $contact = one("SELECT * FROM contacts WHERE company_id = ? AND list_id = ? AND reserved_by = ? AND status IN ('reservado','em_ligacao') AND phone_e164 = ? ORDER BY reserved_at DESC LIMIT 1", [$companyId, $campaign['list_id'], $agentId, $phone]);
            if (!$contact && !$autoDialing) {
                $contact = one("SELECT * FROM contacts WHERE company_id = ? AND list_id = ? AND reserved_by = ? AND status IN ('reservado','em_ligacao') ORDER BY reserved_at DESC LIMIT 1", [$companyId, $campaign['list_id'], $agentId]);
            }
        }
        if ($autoDialing && (!$campaign || !$contact || (string)($campaign['dialer_type'] ?? '') === 'manual')) {
            http_response_code(409);
            echo json_encode_safe(['ok' => false, 'error' => 'A lead automatica nao esta mais reservada nesta campanha. Atualize o discador para continuar a lista.']);
            exit;
        }
        if (!$campaign || !$contact) {
            $campaign = get_or_create_manual_campaign($companyId, $agentId);
            $campaignId = (int)($campaign['id'] ?? 0);
            $listId = (int)($campaign['list_id'] ?? 0);
            $contact = one("SELECT * FROM contacts WHERE company_id = ? AND list_id = ? AND phone_e164 = ? AND status <> 'excluido'", [$companyId, $listId, $phone]);
            if (!$contact) {
                db()->prepare("INSERT INTO contacts (company_id, list_id, name, phone_raw, phone_e164, origin, status, reserved_by, reserved_at, reservation_expires_at)
                    VALUES (?, ?, 'Ligacao manual', ?, ?, 'Manual', 'reservado', ?, datetime('now'), datetime('now', '+10 minutes'))")
                    ->execute([$companyId, $listId, (string)($payload['phone'] ?? $phone), $phone, $agentId]);
                $contact = one('SELECT * FROM contacts WHERE id = ?', [(int)db()->lastInsertId()]);
            }
        }
        if (!$campaignId || !$contact) {
            http_response_code(422);
            echo json_encode_safe(['ok' => false, 'error' => 'Nao foi possivel registrar a chamada SIP.']);
            exit;
        }

        $existing = one("SELECT * FROM calls WHERE company_id = ? AND agent_id = ? AND contact_id = ? AND status IN (" . live_call_statuses_sql() . ") ORDER BY id DESC LIMIT 1", [$companyId, $agentId, $contact['id']]);
        if ($existing) {
            echo json_encode_safe(['ok' => true, 'callId' => (int)$existing['id'], 'status' => $existing['status'], 'call' => call_modal_payload((int)$existing['id'], $companyId, $agentId)]);
            exit;
        }

        $externalId = 'SIP-' . bin2hex(random_bytes(6));
        $billingRateMicros = call_plan_rate_micros($companyId);
        db()->prepare("INSERT INTO calls (company_id, campaign_id, contact_id, agent_id, provider, external_call_id, provider_call_id, destination_number, status, provider_status_raw, internal_status, attempt_number, billing_rate_micros, telephony_period_id, started_at)
            VALUES (?, ?, ?, ?, 'Nvoip SIP/WebRTC', ?, ?, ?, 'in_progress', ?, ?, ?, ?, ?, datetime('now'))")
            ->execute([$companyId, $campaignId, $contact['id'], $agentId, $externalId, $externalId, $phone, 'in_progress', 'iniciada', max(1, (int)($contact['attempts'] ?? 0) + 1), $billingRateMicros, (int)$telephony['state']['period_id']]);
        $callId = (int)db()->lastInsertId();
        db()->prepare("UPDATE contacts SET status = 'em_ligacao', attempts = attempts + 1, last_call_at = datetime('now') WHERE id = ?")->execute([$contact['id']]);
        $nextStatus = (($user['status'] ?? '') === 'Discando automatico') ? 'Discando automatico' : 'Em ligacao';
        db()->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$nextStatus, $agentId]);
        db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'sip.started', 'reserved', 'in_progress', ?)")
            ->execute([$companyId, $callId, json_encode_safe($payload)]);
        echo json_encode_safe(['ok' => true, 'callId' => $callId, 'status' => 'in_progress', 'call' => call_modal_payload($callId, $companyId, $agentId)]);
        exit;
    }

    if ($event === 'progress') {
        $callId = (int)($payload['call_id'] ?? 0);
        $call = $callId ? one('SELECT * FROM calls WHERE id = ? AND company_id = ? AND agent_id = ?', [$callId, $companyId, $agentId]) : null;
        if (!$call) {
            echo json_encode_safe(['ok' => true, 'ignored' => true]);
            exit;
        }
        if (!in_array((string)$call['status'], ['in_progress', 'calling_origin', 'ringing'], true)) {
            echo json_encode_safe(['ok' => true, 'ignored' => true, 'status' => $call['status']]);
            exit;
        }
        $statusCode = (int)($payload['status_code'] ?? 0);
        $ringingConfirmed = !empty($payload['ringing_confirmed']) || $statusCode === 180;
        $newStatus = $ringingConfirmed ? 'ringing' : (string)$call['status'];
        if ($ringingConfirmed) {
            db()->prepare("UPDATE calls SET status = 'ringing', provider_status_raw = 'ringing', internal_status = 'chamando', ringing_at = COALESCE(ringing_at, datetime('now')), updated_at = datetime('now') WHERE id = ?")
                ->execute([$callId]);
        }
        db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'sip.progress', ?, ?, ?)")
            ->execute([$companyId, $callId, $call['status'], $newStatus, json_encode($payload, JSON_UNESCAPED_UNICODE)]);
        log_call_status($companyId, $callId, 'Nvoip SIP/WebRTC', $newStatus, 'sip_progress', $payload);
        echo json_encode_safe(['ok' => true, 'callId' => $callId, 'status' => $newStatus, 'ringing_confirmed' => $ringingConfirmed]);
        exit;
    }

    if ($event === 'answered') {
        $callId = (int)($payload['call_id'] ?? 0);
        $call = $callId ? one('SELECT * FROM calls WHERE id = ? AND company_id = ? AND agent_id = ?', [$callId, $companyId, $agentId]) : null;
        if (!$call) {
            echo json_encode_safe(['ok' => true, 'ignored' => true]);
            exit;
        }
        if (!in_array((string)$call['status'], ['in_progress', 'calling_origin', 'ringing', 'answered'], true)) {
            echo json_encode_safe(['ok' => true, 'ignored' => true, 'status' => $call['status']]);
            exit;
        }
        db()->prepare("UPDATE calls SET status = 'answered', provider_status_raw = 'answered', internal_status = 'atendida', ringing_at = COALESCE(ringing_at, datetime('now')), answered_at = COALESCE(answered_at, datetime('now')), updated_at = datetime('now') WHERE id = ?")
            ->execute([$callId]);
        db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, 'sip.answered', ?, 'answered', ?)")
            ->execute([$companyId, $callId, $call['status'], json_encode($payload, JSON_UNESCAPED_UNICODE)]);
        echo json_encode_safe(['ok' => true, 'callId' => $callId, 'status' => 'answered', 'call' => call_modal_payload($callId, $companyId, $agentId)]);
        exit;
    }

    if (in_array($event, ['ended', 'failed'], true)) {
        $callId = (int)($payload['call_id'] ?? 0);
        $call = $callId ? one('SELECT * FROM calls WHERE id = ? AND company_id = ? AND agent_id = ?', [$callId, $companyId, $agentId]) : null;
        if (!$call) {
            echo json_encode_safe(['ok' => true, 'ignored' => true]);
            exit;
        }
        $cause = strtolower((string)($payload['cause'] ?? ''));
        $rawProviderStatus = trim((string)($payload['cause'] ?? ''));
        if ($rawProviderStatus === '') {
            $rawProviderStatus = trim((string)($payload['sip_reason'] ?? ''));
        }
        if ($rawProviderStatus === '') {
            $rawProviderStatus = $event;
        }
        $sipCode = (int)($payload['sip_code'] ?? 0);
        $sipReason = trim((string)($payload['sip_reason'] ?? ''));
        $errorParts = [];
        if ($sipCode > 0) {
            $errorParts[] = 'SIP ' . $sipCode;
        }
        if ($sipReason !== '' && strcasecmp($sipReason, $rawProviderStatus) !== 0) {
            $errorParts[] = $sipReason;
        }
        if ($rawProviderStatus !== '') {
            $errorParts[] = $rawProviderStatus;
        }
        $providerError = implode(' - ', array_values(array_unique($errorParts)));
        $stoppedByUser = !empty($payload['stopped_by_user']);
        $terminalFailure = !empty($payload['terminal_failure']) || is_terminal_call_failure($cause);
        $wasAnswered = !$terminalFailure && (!empty($call['answered_at']) || !empty($payload['answered']));
        $status = $wasAnswered ? 'pos_atendimento' : unsuccessful_sip_status($cause, $stoppedByUser);
        $duration = $wasAnswered ? call_conversation_duration_seconds($call, time()) : 0;
        $internalStatus = $wasAnswered
            ? 'atendida'
            : normalize_call_attempt_status($status, [
                'cause' => $cause,
                'stopped_by_user' => $stoppedByUser,
                'duration_seconds' => $duration,
                'answered_at' => $call['answered_at'] ?? null,
                'event' => 'ended',
            ]);
        $providerBillable = first_payload_value($payload, ['billsec', 'billable_seconds', 'billable_duration', 'charged_seconds', 'talk_time']);
        $billable = call_billable_seconds($call, $duration, $wasAnswered, $providerBillable);
        $billing = call_billing_values($call, $billable);
        db()->prepare("UPDATE calls SET status = ?, provider_status_raw = ?, internal_status = ?, error_message = NULLIF(?, ''), ended_at = datetime('now'), duration_seconds = ?, billable_seconds = ?, billing_rate_micros = ?, estimated_cost_micros = ?, estimated_cost = ?, updated_at = datetime('now') WHERE id = ?")
            ->execute([$status, $rawProviderStatus, $internalStatus, $providerError, $duration, $billable, $billing['rate_micros'], $billing['cost_micros'], $billing['cost_decimal'], $callId]);
        telephony_record_call_debit($call, $billing, $agentId ?: null);
        $contactStatus = $wasAnswered ? 'pos_atendimento' : 'concluido';
        db()->prepare("UPDATE contacts SET status = ?, reserved_by = NULL, reserved_at = NULL, reservation_expires_at = NULL WHERE id = ?")
            ->execute([$contactStatus, $call['contact_id']]);
        if ($wasAnswered) {
            db()->prepare("UPDATE users SET status = 'Pos-atendimento' WHERE id = ? AND status <> 'Discando automatico'")->execute([$agentId]);
        } elseif (!empty($payload['stopped_by_user'])) {
            db()->prepare("UPDATE users SET status = 'Disponivel' WHERE id = ?")->execute([$agentId]);
        } elseif (($user['status'] ?? '') !== 'Discando automatico') {
            db()->prepare("UPDATE users SET status = 'Disponivel' WHERE id = ?")->execute([$agentId]);
        }
        db()->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$companyId, $callId, 'sip.' . $event, $call['status'], $status, json_encode($payload, JSON_UNESCAPED_UNICODE)]);
        log_call_status($companyId, $callId, 'Nvoip SIP/WebRTC', $status, $wasAnswered ? 'sip_call_answered_ended' : 'sip_terminal_without_success', $payload);

        $agentStatus = (string)(one('SELECT status FROM users WHERE id = ?', [$agentId])['status'] ?? '');
        $autoDialing = !empty($payload['auto_dialing']) || $agentStatus === 'Discando automatico';
        $callCampaign = one('SELECT dialer_type FROM campaigns WHERE id = ? AND company_id = ?', [(int)$call['campaign_id'], $companyId]);
        $isManualCampaign = (($callCampaign['dialer_type'] ?? '') === 'manual');
        $response = ['ok' => true, 'callId' => $callId, 'status' => $status];
        $shouldContinue = !$wasAnswered
            && empty($payload['stopped_by_user'])
            && $autoDialing
            && !$isManualCampaign
            && (int)$call['campaign_id'] > 0;
        if ($shouldContinue) {
            $campaign = one('SELECT * FROM campaigns WHERE id = ? AND company_id = ? AND status = "Ativa"', [$call['campaign_id'], $companyId]);
            $nextContact = $campaign ? one("SELECT * FROM contacts
                WHERE company_id = ? AND list_id = ? AND reserved_by = ? AND status = 'reservado'
                ORDER BY reserved_at DESC, id ASC LIMIT 1", [$companyId, $campaign['list_id'], $agentId]) : null;
            if (!$nextContact && $campaign) {
                $nextContact = next_eligible_contact($campaign, $companyId);
            }
            if ($nextContact) {
                if ((string)$nextContact['status'] !== 'reservado' || (int)($nextContact['reserved_by'] ?? 0) !== $agentId) {
                    db()->prepare("UPDATE contacts SET reserved_by = ?, reserved_at = datetime('now'), reservation_expires_at = datetime('now', '+10 minutes'), status = 'reservado' WHERE id = ?")
                        ->execute([$agentId, $nextContact['id']]);
                    audit('discador_reservou_contato_auto', 'contacts:' . $nextContact['id']);
                }
                $response['continue_auto'] = true;
                $response['next_phone'] = $nextContact['phone_e164'];
            } else {
                db()->prepare("UPDATE users SET status = 'Disponivel' WHERE id = ?")->execute([$agentId]);
                $response['continue_auto'] = false;
                $response['queue_empty'] = true;
            }
        }
        echo json_encode_safe($response);
        exit;
    }

    http_response_code(422);
    echo json_encode_safe(['ok' => false, 'error' => 'Evento SIP invalido.']);
    exit;
}

function handle_quick_block_call(): never
{
    require_login();
    $user = current_user();
    header('Content-Type: application/json; charset=utf-8');
    if (!$user || !can('agent')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Sem permissao para bloquear este numero.']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
    $callId = (int)($payload['call_id'] ?? 0);
    $params = [$callId, (int)$user['company_id']];
    $agentFilter = '';
    if (!is_platform_admin($user)) {
        $agentFilter = ' AND agent_id = ?';
        $params[] = (int)$user['id'];
    }
    $call = one("SELECT id, destination_number FROM calls WHERE id = ? AND company_id = ?{$agentFilter}", $params);
    $phone = $call ? normalize_phone((string)$call['destination_number']) : null;
    if (!$call || !$phone) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Chamada ou telefone nao encontrado.']);
        exit;
    }

    db()->prepare("INSERT OR IGNORE INTO blocklist (company_id, phone_e164, reason, source, responsible_user_id, notes) VALUES (?, ?, 'Bloqueado durante atendimento', 'modal_atendimento', ?, 'Incluido pela modal de chamada atendida')")
        ->execute([(int)$user['company_id'], $phone, (int)$user['id']]);
    audit('incluiu_bloqueio_atendimento', 'blocklist:' . $phone, null, ['call_id' => $callId]);
    echo json_encode(['ok' => true, 'phone' => $phone, 'message' => 'Numero adicionado aos bloqueados.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_GET['page'] ?? '') === 'mercado_pago_webhook') {
    handle_mercado_pago_webhook();
}

if (($_GET['page'] ?? '') === 'nvoip_webhook') {
    handle_nvoip_webhook();
}

if (($_GET['page'] ?? '') === 'password_reset_confirm') {
    confirm_password_reset((string)($_GET['token'] ?? ''));
}

$requestedPage = (string)($_GET['page'] ?? 'dashboard');
$billingUser = current_user();
if ($billingUser && !is_platform_admin($billingUser)) {
    $billingState = tenant_billing_state((int)$billingUser['company_id']);
    if (!billing_operational_route_allowed((bool)$billingState['blocked'], $requestedPage) && !isset($_GET['logout'])) {
        if (in_array($requestedPage, ['sip_config','sip_call_event','phone_history','list_contacts_batch','answered_calls_batch','agent_batch_state','quick_block_call'], true)) {
            header('Content-Type: application/json; charset=utf-8'); http_response_code(402); echo json_encode(['ok'=>false,'error'=>'Plano bloqueado.']); exit;
        }
        flash((string)$billingState['message'], 'error');
        redirect('?page=costs');
    }
}

if (($_GET['page'] ?? '') === 'sip_config') {
    handle_sip_config();
}

if (($_GET['page'] ?? '') === 'quick_block_call') {
    handle_quick_block_call();
}

if (($_GET['page'] ?? '') === 'phone_history') {
    handle_phone_history();
}

if (($_GET['page'] ?? '') === 'payment_status') {
    handle_payment_status();
}

if (($_GET['page'] ?? '') === 'list_contacts_batch') {
    handle_list_contacts_batch();
}

if (($_GET['page'] ?? '') === 'callback_notifications') {
    handle_callback_notifications();
}

if (($_GET['page'] ?? '') === 'sip_call_event') {
    handle_sip_call_event();
}

if (($_GET['page'] ?? '') === 'recording_file') {
    handle_recording_file();
}

if (($_GET['page'] ?? '') === 'asterisk_diagnostics_data') {
    handle_asterisk_diagnostics_data();
}

if (($_GET['page'] ?? '') === 'lists' && isset($_GET['download_template'])) {
    download_csv_template();
}

function asterisk_event_key(array $event): string
{
    $channelId = (string)($event['channel']['id'] ?? $event['channel']['name'] ?? '');
    return hash('sha256', implode('|', [(string)($event['timestamp'] ?? ''), (string)($event['type'] ?? ''), $channelId, json_encode_safe($event)]));
}

function asterisk_extension_from_identifier(mixed $identifier): string
{
    if (!is_string($identifier) && !is_numeric($identifier)) return '';
    $value = trim((string)$identifier);
    if ($value === '') return '';
    if (preg_match('/^PJSIP\/([0-9]{1,32})(?:[-@\/]|$)/i', $value, $matches) === 1) return $matches[1];
    if (preg_match('/^([0-9]{1,32})(?:[-@]|$)/', $value, $matches) === 1) return $matches[1];
    return '';
}

function asterisk_event_extension(array $event): string
{
    $endpoint = $event['endpoint'] ?? null;
    $candidates = [
        $event['channel']['name'] ?? null,
        is_array($endpoint) ? ($endpoint['resource'] ?? null) : $endpoint,
    ];
    foreach ($candidates as $candidate) {
        $extension = asterisk_extension_from_identifier($candidate);
        if ($extension !== '') return $extension;
    }
    return '';
}

function asterisk_associate_call_user(PDO $pdo, array $call, string $extension): ?int
{
    if ($extension === '') return null;
    $companyId = (int)$call['company_id'];
    $currentUserId = (int)($call['agent_id'] ?? 0);
    if ($currentUserId > 0 && one('SELECT id FROM users WHERE id = ? AND company_id = ?', [$currentUserId, $companyId])) return $currentUserId;

    // The current architecture has one configured Asterisk server, represented by ID 1.
    $link = one("SELECT user_id FROM asterisk_user_extensions WHERE company_id = ? AND asterisk_server_id = 1 AND extension = ? AND status = 'Ativo' AND COALESCE(lifecycle_status, 'ACTIVE') = 'ACTIVE' LIMIT 1", [$companyId, $extension]);
    if (!$link) return null;
    $userId = (int)$link['user_id'];
    $pdo->prepare('UPDATE calls SET agent_id = ? WHERE id = ? AND company_id = ? AND (agent_id IS NULL OR agent_id = 0)')
        ->execute([$userId, (int)$call['id'], $companyId]);
    if (!empty($call['dial_batch_id'])) {
        $pdo->prepare('UPDATE dial_batches SET agent_id = ? WHERE id = ? AND company_id = ? AND (agent_id IS NULL OR agent_id = 0)')
            ->execute([$userId, (int)$call['dial_batch_id'], $companyId]);
    }
    return $userId;
}

function asterisk_normalized_event_status(array $event): array
{
    $type = (string)($event['type'] ?? '');
    $state = strtolower((string)($event['channel']['state'] ?? ''));
    if ($type === 'StasisStart') return ['in_progress', 'iniciada'];
    if ($type === 'ChannelAnswered' || $state === 'up') return ['answered', 'atendida'];
    if ($type === 'ChannelStateChange' && in_array($state, ['ring', 'ringing'], true)) return ['ringing', 'chamando'];
    if (in_array($type, ['ChannelDestroyed', 'StasisEnd'], true)) {
        $cause = (int)($event['cause'] ?? $event['channel']['cause'] ?? 0);
        return [in_array($cause, [16, 0], true) ? 'completed' : 'failed', in_array($cause, [16, 0], true) ? 'atendida' : 'falha'];
    }
    return ['', ''];
}

function asterisk_event_transition(array $event, array $call): array
{
    $type = strtolower((string)($event['type'] ?? ''));
    $state = strtolower((string)($event['channel']['state'] ?? ''));
    $cause = (int)($event['cause'] ?? $event['channel']['cause'] ?? -1);
    $answered = !empty($call['answered_at']) || in_array((string)$call['internal_status'], ['atendida', 'conectada'], true);
    if ($type === 'channelcreated' || $type === 'stasisstart') return ['in_progress', 'iniciada', false];
    if ($type === 'channelstatechange' && in_array($state, ['ring', 'ringing'], true)) return ['ringing', 'chamando', false];
    if ($type === 'channelanswered' || ($type === 'channelstatechange' && $state === 'up')) return ['answered', 'atendida', false];
    if ($type === 'bridgeenter' && !empty($event['bridge']['id'])) return ['connected', 'conectada', false];
    if (!in_array($type, ['channeldestroyed', 'stasisend'], true)) return ['', '', false];
    if (in_array($cause, [17], true)) return ['busy', 'ocupado', true];
    if (in_array($cause, [18, 19], true)) return ['no_answer', 'nao_atendida', true];
    if (in_array($cause, [1, 3, 28], true)) return ['invalid_number', 'numero_inexistente', true];
    if (in_array($cause, [21], true)) return ['failed', 'falha', true];
    if (in_array($cause, [16, 0], true)) return [$answered ? 'completed' : 'no_answer', $answered ? 'atendida' : 'nao_atendida', true];
    return ['failed', 'falha', true];
}

function asterisk_handle_event(array $event): void
{
    $eventKey = asterisk_event_key($event);
    $channelId = (string)($event['channel']['id'] ?? '');
    $linkedId = (string)($event['channel']['linkedid'] ?? '');
    $detectedExtension = asterisk_event_extension($event);
    $eventType = (string)($event['type'] ?? 'unknown');
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $saved = $pdo->prepare("INSERT OR IGNORE INTO asterisk_ari_events (event_key, event_type, payload_json) VALUES (?, ?, ?)");
        $saved->execute([$eventKey, $eventType, json_encode_safe($event)]);
        if ($saved->rowCount() === 0) { $pdo->commit(); return; }
        $call = $channelId !== '' ? one('SELECT * FROM calls WHERE provider_channel_id = ? ORDER BY id DESC LIMIT 1', [$channelId]) : null;
        if (!$call && $linkedId !== '') $call = one('SELECT * FROM calls WHERE provider_linked_id = ? ORDER BY id DESC LIMIT 1', [$linkedId]);
        if (!$call || (string)($call['telephony_mode'] ?? '') !== 'ASTERISK') { $pdo->commit(); return; }
        $resolvedUserId = asterisk_associate_call_user($pdo, $call, $detectedExtension);
        if ($detectedExtension !== '' || $resolvedUserId !== null) {
            $event['_ligflow'] = ['detected_extension' => $detectedExtension, 'resolved_user_id' => $resolvedUserId];
            $pdo->prepare('UPDATE asterisk_ari_events SET payload_json = ? WHERE event_key = ?')->execute([json_encode_safe($event), $eventKey]);
        }
        $pdo->prepare('UPDATE asterisk_ari_events SET call_id = ? WHERE event_key = ?')->execute([(int)$call['id'], $eventKey]);
        $terminal = !empty($call['finalized_at']);
        [$status, $internal, $isFinal] = asterisk_event_transition($event, $call);
        if ($status === '' || $terminal) { $pdo->commit(); return; }
        $rawCause = trim((string)($event['cause_txt'] ?? $event['cause'] ?? ''));
        $sets = ['status = ?', 'provider_status_raw = ?', 'internal_status = ?', "provider_channel_id = COALESCE(NULLIF(?, ''), provider_channel_id)", "provider_linked_id = COALESCE(NULLIF(?, ''), provider_linked_id)", "last_event_at = datetime('now')", "event_origin = 'ASTERISK_ARI'"];
        $params = [$status, $eventType . ($rawCause !== '' ? ' - ' . $rawCause : ''), $internal, $channelId, $linkedId];
        if ($internal === 'atendida') $sets[] = "answered_at = COALESCE(answered_at, datetime('now'))";
        if ($internal === 'conectada') { $sets[] = "answered_at = COALESCE(answered_at, datetime('now'))"; $sets[] = "connected_at = COALESCE(connected_at, datetime('now'))"; }
        if ($isFinal) { $sets[] = "ended_at = COALESCE(ended_at, datetime('now'))"; $sets[] = "finalized_at = COALESCE(finalized_at, datetime('now'))"; $sets[] = 'hangup_cause = ?'; $sets[] = "error_message = COALESCE(NULLIF(?, ''), error_message)"; $params[] = $rawCause; $params[] = $rawCause; }
        $params[] = (int)$call['id'];
        $pdo->prepare('UPDATE calls SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
        $pdo->prepare("INSERT INTO call_events (company_id, call_id, event_name, old_status, new_status, payload) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([(int)$call['company_id'], (int)$call['id'], 'asterisk.' . strtolower($eventType), (string)$call['status'], $status, json_encode_safe($event)]);
        $pdo->commit();
        if ($internal === 'atendida' && !empty($call['dial_batch_id'])) asterisk_batch_answered((int)$call['id']);
        if ($isFinal) {
            $updated = one('SELECT * FROM calls WHERE id = ? AND company_id = ?', [(int)$call['id'], (int)$call['company_id']]);
            if ($updated) {
                $answered = !empty($updated['answered_at']);
                $answeredAt = utc_storage_timestamp((string)($updated['answered_at'] ?? ''));
                $duration = $answered && $answeredAt !== false ? max(0, time() - $answeredAt) : 0;
                $billable = call_billable_seconds($updated, $duration, $answered, $event['billsec'] ?? $event['billable_seconds'] ?? null);
                $billing = call_billing_values($updated, $billable);
                db()->prepare("UPDATE calls SET duration_seconds = ?, billable_seconds = ?, estimated_cost_micros = ?, confirmed_cost = ? WHERE id = ? AND finalized_at IS NOT NULL")
                    ->execute([$duration, $billable, (int)$billing['cost_micros'], (int)$billing['cost_micros'] / 1000000, (int)$updated['id']]);
                telephony_record_call_debit($updated, $billing, (int)$updated['agent_id']);
            }
        }
        if ($isFinal && !empty($call['dial_batch_id'])) asterisk_continue_batch_if_exhausted((int)$call['dial_batch_id']);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
final class AsteriskAriWebSocket
{
    private $socket = null;
    public function __construct(private array $config) {}
    public function connect(): void
    {
        $url = (string)($this->config['ari_ws_url'] ?? '');
        if ($url === '') throw new RuntimeException('URL WebSocket ARI nao configurada.');
        $parts = parse_url($url);
        if (!$parts || !in_array($parts['scheme'] ?? '', ['ws', 'wss'], true) || empty($parts['host'])) throw new RuntimeException('URL WebSocket ARI invalida.');
        $scheme = ($parts['scheme'] === 'wss') ? 'tls' : 'tcp';
        $port = (int)($parts['port'] ?? ($parts['scheme'] === 'wss' ? 443 : 80));
        $errno = 0; $error = '';
        $this->socket = @stream_socket_client($scheme . '://' . $parts['host'] . ':' . $port, $errno, $error, 10, STREAM_CLIENT_CONNECT);
        if (!$this->socket) throw new RuntimeException('Nao foi possivel conectar ao WebSocket ARI.');
        stream_set_timeout($this->socket, 10);
        $path = ($parts['path'] ?? '/ari/events');
        parse_str($parts['query'] ?? '', $query);
        $query['app'] = (string)$this->config['stasis_app'];
        $query['api_key'] = (string)$this->config['ari_username'] . ':' . (string)$this->config['ari_password'];
        $path .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $key = base64_encode(random_bytes(16));
        $host = $parts['host'] . (($port === 80 || $port === 443) ? '' : ':' . $port);
        fwrite($this->socket, "GET {$path} HTTP/1.1\r\nHost: {$host}\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Key: {$key}\r\nSec-WebSocket-Version: 13\r\n\r\n");
        $response = '';
        while (!str_contains($response, "\r\n\r\n") && !feof($this->socket)) $response .= (string)fgets($this->socket);
        if (!str_contains($response, ' 101 ')) { $this->close(); throw new RuntimeException('Handshake WebSocket ARI recusado.'); }
    }
    private function bytes(int $length): string
    {
        $data = '';
        while (strlen($data) < $length && !feof($this->socket)) { $part = fread($this->socket, $length - strlen($data)); if ($part === false || $part === '') break; $data .= $part; }
        return $data;
    }
    public function readEvent(int $timeoutSeconds = 10): ?array
    {
        if (!$this->socket) return null;
        stream_set_timeout($this->socket, $timeoutSeconds);
        $head = $this->bytes(2); if (strlen($head) < 2) return null;
        $opcode = ord($head[0]) & 0x0f; $length = ord($head[1]) & 0x7f;
        if ($length === 126) { $raw = $this->bytes(2); if (strlen($raw) < 2) return null; $length = unpack('n', $raw)[1]; }
        elseif ($length === 127) { $raw = $this->bytes(8); if (strlen($raw) < 8) return null; $size = unpack('N2', $raw); $length = $size[1] * 4294967296 + $size[2]; }
        if ($length > 1048576) throw new RuntimeException('Evento ARI excede o limite permitido.');
        $payload = $this->bytes((int)$length);
        if ($opcode === 8) return null;
        if ($opcode !== 1 || $payload === '') return null;
        $event = json_decode($payload, true);
        return is_array($event) ? $event : null;
    }
    public function close(): void { if (is_resource($this->socket)) fclose($this->socket); $this->socket = null; }
}
function valid_asterisk_provisioning_agent_url(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $parts = parse_url($url);
    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    $host = strtolower((string)($parts['host'] ?? ''));
    return $scheme === 'https' || ($scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], true));
}

function asterisk_provisioning_safe_error(string $message): string
{
    $message = preg_replace('/(?:password|secret|authorization|token)\s*[:=]\s*\S+/i', '[redacted]', $message) ?? 'provisioning_failed';
    return substr(trim($message), 0, 300);
}

function asterisk_agent_create_extension(array $config, array $job, array $extension): array
{
    $url = (string)($config['provisioning_agent_url'] ?? '');
    $secret = (string)($config['provisioning_agent_secret'] ?? '');
    $sipPassword = decrypt_secret((string)($extension['sip_password_encrypted'] ?? ''));
    if (!valid_asterisk_provisioning_agent_url($url) || $secret === '' || $sipPassword === '') {
        throw new RuntimeException('Agente de provisionamento ou credencial SIP nao configurados.');
    }
    if (!function_exists('curl_init')) throw new RuntimeException('A extensao cURL e obrigatoria para provisionar ramais.');
    $payload = json_encode_safe([
        'operation' => 'CREATE_EXTENSION',
        'idempotency_key' => (string)$job['idempotency_key'],
        'asterisk_server_id' => (int)$job['asterisk_server_id'],
        'asterisk_user_extension_id' => (int)$job['asterisk_user_extension_id'],
        'extension' => (string)$extension['extension'],
        'sip_password' => $sipPassword,
    ]);
    $timestamp = (string)time();
    $nonce = bin2hex(random_bytes(16));
    $signature = hash_hmac('sha256', $timestamp . '.' . $nonce . '.' . $payload, $secret);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => min(10, (int)$config['provisioning_agent_timeout_seconds']),
        CURLOPT_TIMEOUT => (int)$config['provisioning_agent_timeout_seconds'],
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-LigFlow-Timestamp: ' . $timestamp, 'X-LigFlow-Nonce: ' . $nonce, 'X-LigFlow-Signature: ' . $signature],
    ]);
    $body = curl_exec($ch); $error = curl_error($ch); $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE); curl_close($ch);
    if ($body === false || $error !== '') throw new RuntimeException('Falha de comunicacao com o agente.');
    $response = json_decode((string)$body, true);
    if (!is_array($response)) throw new RuntimeException('Resposta invalida do agente.');
    return ['http_status' => $status, 'body' => $response];
}

function asterisk_claim_next_provisioning_job(PDO $pdo): ?array
{
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $pdo->prepare("UPDATE asterisk_provisioning_jobs SET status = 'PENDING', processing_started_at = NULL, updated_at = datetime('now') WHERE status = 'PROCESSING' AND processing_started_at < datetime('now', '-5 minutes')")->execute();
        $job = $pdo->query("SELECT * FROM asterisk_provisioning_jobs WHERE operation = 'CREATE' AND status = 'PENDING' ORDER BY id ASC LIMIT 1")->fetch();
        if (!$job) { $pdo->exec('COMMIT'); return null; }
        $claim = $pdo->prepare("UPDATE asterisk_provisioning_jobs SET status = 'PROCESSING', attempts = attempts + 1, processing_started_at = datetime('now'), updated_at = datetime('now') WHERE id = ? AND status = 'PENDING'");
        $claim->execute([(int)$job['id']]);
        if ($claim->rowCount() !== 1) { $pdo->exec('COMMIT'); return null; }
        $pdo->exec('COMMIT');
        $job['attempts'] = (int)$job['attempts'] + 1;
        return $job;
    } catch (Throwable $error) { if ($pdo->inTransaction()) $pdo->exec('ROLLBACK'); throw $error; }
}

function asterisk_finalize_provisioning_job(PDO $pdo, array $job, bool $success, string $error = '', array $response = []): void
{
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $extension = $pdo->prepare('SELECT * FROM asterisk_user_extensions WHERE id = ? AND company_id = ? AND user_id = ? AND asterisk_server_id = ?');
        $extension->execute([(int)$job['asterisk_user_extension_id'], (int)$job['company_id'], (int)$job['user_id'], (int)$job['asterisk_server_id']]);
        $link = $extension->fetch();
        if (!$link) throw new RuntimeException('Vinculo de ramal nao encontrado.');
        $safeResponse = json_encode_safe(['ok' => !empty($response['ok']), 'extension' => (string)($response['extension'] ?? $link['extension']), 'endpoint_confirmed' => !empty($response['endpoint_confirmed'])]);
        if ($success && strtoupper((string)$link['lifecycle_status']) === 'RESERVED') {
            $pdo->prepare("UPDATE asterisk_user_extensions SET lifecycle_status = 'ACTIVE', provisioning_status = 'Concluido', provisioned_at = datetime('now'), last_provision_error = NULL, updated_at = datetime('now') WHERE id = ?")->execute([(int)$link['id']]);
            $pdo->prepare("UPDATE asterisk_provisioning_jobs SET status = 'SUCCESS', last_error = NULL, response_json = ?, completed_at = datetime('now'), updated_at = datetime('now') WHERE id = ?")->execute([$safeResponse, (int)$job['id']]);
        } else {
            $safeError = asterisk_provisioning_safe_error($error ?: 'provisioning_failed');
            $pdo->prepare("UPDATE asterisk_user_extensions SET provisioning_status = 'Falhou', last_provision_error = ?, updated_at = datetime('now') WHERE id = ?")->execute([$safeError, (int)$link['id']]);
            $pdo->prepare("UPDATE asterisk_provisioning_jobs SET status = 'FAILED', last_error = ?, response_json = ?, completed_at = datetime('now'), updated_at = datetime('now') WHERE id = ?")->execute([$safeError, $safeResponse, (int)$job['id']]);
        }
        $pdo->exec('COMMIT');
    } catch (Throwable $error) { if ($pdo->inTransaction()) $pdo->exec('ROLLBACK'); throw $error; }
}

function asterisk_process_pending_provisioning_jobs(int $limit = 10): int
{
    $pdo = db(); $processed = 0;
    while ($processed < max(1, min(100, $limit))) {
        $job = asterisk_claim_next_provisioning_job($pdo);
        if (!$job) break;
        try {
            $extension = one('SELECT * FROM asterisk_user_extensions WHERE id = ? AND company_id = ? AND user_id = ?', [(int)$job['asterisk_user_extension_id'], (int)$job['company_id'], (int)$job['user_id']]);
            if (!$extension || strtoupper((string)$extension['lifecycle_status']) !== 'RESERVED') throw new RuntimeException('Vinculo nao esta reservado para provisionamento.');
            $result = asterisk_agent_create_extension(asterisk_server_config((int)$job['asterisk_server_id']), $job, $extension);
            if ((int)$result['http_status'] >= 200 && (int)$result['http_status'] < 300 && !empty($result['body']['ok']) && !empty($result['body']['endpoint_confirmed'])) {
                asterisk_finalize_provisioning_job($pdo, $job, true, '', $result['body']);
            } else {
                asterisk_finalize_provisioning_job($pdo, $job, false, (string)($result['body']['error'] ?? 'agent_rejected'), $result['body']);
            }
        } catch (Throwable $error) {
            asterisk_finalize_provisioning_job($pdo, $job, false, $error->getMessage());
        }
        $processed++;
    }
    return $processed;
}
if (!defined('LIGFLOW_ARI_WORKER')) {
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    handle_post();
}

if (isset($_GET['logout'])) {
    audit('logout');
    session_destroy();
    redirect('?page=login');
}

$page = $_GET['page'] ?? 'dashboard';
if ($page === 'sip_diagnostic') {
    redirect('?page=settings&sip=1#diagnostico-sip');
}
if ($page !== 'login') {
    require_login();
    $mergedCallsAccess = ($page === 'supervisor' && can('recordings'))
        || ($page === 'recordings' && can('supervisor'));
    $agentEndpointAccess = in_array($page, ['answered_calls_batch', 'agent_batch_state'], true) && can('agent');
    if (!can($page) && !$mergedCallsAccess && !$agentEndpointAccess) {
        flash('Voce nao tem permissao para acessar esta area.', 'error');
        redirect('?page=dashboard');
    }
}

function layout(string $page, callable $content): void
{
    $user = current_user();
    $flash = flash();
    $activeOperation = false;
    $billingState = null;
    $termsRequired = false;
    if ($user) {
        $activeOperation = in_array((string)($user['status'] ?? ''), ['Discando automatico', 'Em ligacao'], true)
            || (bool)get_live_call((int)$user['id'], (int)$user['company_id']);
        if (!is_platform_admin($user)) $billingState = tenant_billing_state((int)$user['company_id']);
        $termsRequired = !user_has_accepted_current_terms($user);
    }
    $titles = [
        'companies' => 'Clientes',
        'plans' => 'Planos',
        'users' => 'Acessos e consultores',
        'lists' => 'Contatos e Listas',
        'settings' => 'Integracoes',
        'asterisk_diagnostics' => 'Diagnostico Asterisk',
        'agent' => 'Discador',
        'campaigns' => 'Campanhas',
        'radar' => 'Radar de Leads',
        'supervisor' => 'Chamadas',
        'costs' => 'Plano e consumo',
        'account' => 'Minha conta',
    ];
    $title = $titles[$page] ?? ucfirst(str_replace('_', ' ', $page));
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(APP_NAME . ' - ' . $title) ?></title>
    <link rel="stylesheet" href="assets/styles.css?v=<?= (int)(@filemtime(__DIR__ . '/assets/styles.css') ?: 1) ?>">
</head>
<body class="<?= $user ? 'app-shell' : 'login-shell' ?><?= $billingState && in_array($billingState['state'], ['warning','overdue','blocked'], true) ? ' billing-banner-active' : '' ?><?= $termsRequired ? ' terms-acceptance-required' : '' ?>">
<?php if ($user): ?>
    <aside class="sidebar">
        <div class="brand">
            <span class="brand-mark"><img src="assets/img/logo-ligflow.png" alt=""></span>
            <div><strong>Lig Flow</strong><small>Prospecção Inteligente</small></div>
        </div>
        <nav>
            <?php
            $items = [
                'dashboard' => 'Inicio',
                'companies' => 'Clientes',
                'plans' => 'Planos',
                'users' => 'Acessos',
                'lists' => 'Contatos e Listas',
                'campaigns' => 'Campanhas',
                'radar' => 'Radar de Leads',
                'agent' => 'Discador',
                'supervisor' => 'Chamadas',
                'reports' => 'Relatorios',
                'costs' => 'Plano e consumo',
                'settings' => 'Integracoes',
                'asterisk_diagnostics' => 'Diagnostico Asterisk',
                'blocklist' => 'Bloqueio',
                'audit' => 'Auditoria',
                'account' => 'Minha conta',
            ];
            foreach ($items as $key => $label):
                if (!can($key) && !($key === 'supervisor' && can('recordings'))) {
                    continue;
                }
            ?>
                <a class="<?= trim(($page === $key ? 'active ' : '') . ($key === 'agent' ? 'nav-discador ' : '') . ($key === 'account' ? 'nav-account' : '')) ?>" href="?page=<?= $key ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <main>
        <?php if ($billingState && in_array($billingState['state'], ['warning','overdue','blocked'], true)): ?>
            <div class="billing-banner billing-<?= h($billingState['state']) ?>">
                <span><?= h((string)$billingState['message']) ?></span>
                <a href="?page=costs">Ver plano e pagar</a>
            </div>
        <?php endif; ?>
        <?php if ($activeOperation): ?>
            <div class="active-call-banner">
                <strong>Ligacoes ativas</strong>
                <span>O discador esta realizando chamadas neste momento.</span>
            </div>
        <?php endif; ?>
        <header class="topbar">
            <div>
                <h1><?= h($title) ?></h1>
                <p><?= h(role_label($user['role'])) ?> - <?= h($user['name']) ?></p>
            </div>
            <div class="topbar-account">
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Alternar tema">
                    <span class="theme-toggle-knob"><span class="theme-icon moon">&#9790;</span><span class="theme-icon sun">&#9728;</span></span>
                </button>
                <div class="user-menu" data-user-menu>
                    <button class="account-chip user-menu-trigger" type="button" data-user-menu-toggle aria-expanded="false">
                        <span class="avatar-wrap"><?= avatar_markup($user) ?><span class="online-dot"></span></span>
                        <span><?= h($user['name']) ?></span>
                    </button>
                    <div class="user-menu-panel" data-user-menu-panel>
                        <div class="user-menu-head">
                            <span class="avatar-wrap"><?= avatar_markup($user) ?><span class="online-dot"></span></span>
                            <strong><?= h($user['name']) ?></strong>
                        </div>
                        <div class="user-menu-list">
                            <div class="user-menu-item user-menu-status"><span class="menu-icon online-icon"></span><span>Online</span></div>
                            <a class="user-menu-item" href="?page=account"><span class="menu-icon">&#9998;</span><span>Editar perfil</span></a>
                            <?php if (can('costs')): ?>
                                <a class="user-menu-item" href="?page=costs"><span class="menu-icon">$</span><span>Assinatura</span></a>
                            <?php endif; ?>
                            <a class="user-menu-item" href="?logout=1"><span class="menu-icon">&#9211;</span><span>Sair</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <?php if ($flash): ?><div class="flash <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div><?php endif; ?>
        <?php $content(); ?>
        <?php if ($page !== 'agent') render_floating_webphone_panel(); ?>
    </main>
<?php else: ?>
    <?php if ($flash): ?><div class="flash login-flash <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div><?php endif; ?>
    <?php $content(); ?>
<?php endif; ?>
<?php if ($user): ?><?php render_terms_modal($user); ?><?php endif; ?>
<script src="assets/app.js?v=<?= (int)(@filemtime(__DIR__ . '/assets/app.js') ?: 1) ?>"></script>
<?php if ($user && can('agent')): ?>
<script src="assets/vendor/jssip.min.js"></script>
<script src="assets/nvoip-webphone.js?v=<?= (int)(@filemtime(__DIR__ . '/assets/nvoip-webphone.js') ?: 1) ?>"></script>
<?php endif; ?>
</body>
</html>
    <?php
}

function role_label(string $role): string
{
    return [
        'admin_plataforma' => 'Administrador da plataforma',
        'admin_geral' => 'Administrador da plataforma',
        'cliente_admin' => 'Cliente admin',
        'admin_empresa' => 'Cliente admin',
        'usuario_operacional' => 'Usuario operacional',
        'supervisor' => 'Supervisor',
        'atendente' => 'Consultor',
    ][$role] ?? $role;
}

function module_checkboxes(array $selected, string $name = 'modules'): string
{
    $selected = sanitize_modules($selected);
    $html = '<div class="module-grid wide">';
    foreach (access_modules() as $key => $label) {
        if ($key === 'account') {
            continue;
        }
        $checked = in_array($key, $selected, true) ? ' checked' : '';
        $html .= '<label class="module-option"><input type="checkbox" name="' . h($name) . '[]" value="' . h($key) . '"' . $checked . '> <span>' . h($label) . '</span></label>';
    }
    $html .= '<p class="hint wide">Minha conta fica sempre liberado para o usuario editar seus proprios dados.</p>';
    $html .= '</div>';
    return $html;
}

function selected_campaign_id(): int
{
    $user = current_user();
    $campaignId = (int)($_GET['campaign_id'] ?? 0);
    if ($campaignId) {
        return $campaignId;
    }
    $clause = tenant_clause();
    return (int)(one("SELECT id FROM campaigns WHERE {$clause[0]} AND status = 'Ativa' ORDER BY id DESC LIMIT 1", $clause[1])['id'] ?? 0);
}

function render_phone_history_items(array $items, string $empty): void
{
    if (!$items) {
        echo '<p class="phone-empty">' . h($empty) . '</p>';
        return;
    }
    foreach ($items as $index => $call) {
        $location = trim((string)($call['city'] ?: $call['state'] ?: 'Contato'), ' /');
        $duration = (int)($call['duration_seconds'] ?? 0);
        $durationText = $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '';
        $time = $call['created_at'] ? datetime_utc_display((string)$call['created_at'], 'H:i:s') : '';
        $meta = trim($location . ($time ? ' - ' . $time : '') . ($durationText ? ' - ' . $durationText : ''));
        $badgeClass = ['green', 'orange', 'blue'][$index % 3];
        ?>
        <button type="button" class="phone-history-item" data-fill-phone="<?= h($call['destination_number']) ?>" data-phone-search="<?= h(strtolower(($call['contato'] ?? '') . ' ' . ($call['destination_number'] ?? '') . ' ' . $meta)) ?>">
            <span class="phone-history-badge <?= h($badgeClass) ?>"><?= h((string)max(1, 5 - $index)) ?></span>
            <span class="phone-history-main">
                <strong><?= h($call['destination_number']) ?></strong>
                <small><?= h($meta ?: ($call['resultado'] ?: $call['status'])) ?></small>
            </span>
            <span class="phone-history-actions">&#9742; &#8942;</span>
        </button>
        <?php
    }
}

function render_floating_webphone_panel(): void
{
    $user = current_user();
    if (!$user || !can('agent')) {
        return;
    }
    $campaignId = selected_campaign_id();
    $activeCall = get_active_call((int)$user['id'], (int)$user['company_id']);
    $lastCall = one("SELECT co.*, ct.name contato FROM calls co LEFT JOIN contacts ct ON ct.id = co.contact_id WHERE co.agent_id = ? ORDER BY co.id DESC LIMIT 1", [$user['id']]);
    $recentHistory = recent_phone_history((int)$user['company_id'], (int)$user['id']);
    $recentCalls = $recentHistory['todas'];
    $recentReceived = $recentHistory['recebidas'];
    $recentMade = $recentHistory['realizadas'];
    $recentMissed = $recentHistory['perdidas'];
    $phoneContacts = rows("SELECT name, phone_e164, product, status FROM contacts WHERE company_id = ? AND status <> 'excluido' ORDER BY last_call_at DESC, id DESC LIMIT 8", [(int)$user['company_id']]);
    ?>
    <section class="webphone-panel" data-sip-floating>
        <button class="webphone-launcher" type="button" data-webphone-toggle aria-label="Abrir webfone">&#10303;</button>
        <article class="webphone is-hidden" data-webphone>
            <header>
                <div class="webphone-title"><span class="status-dot" data-floating-sip-dot></span><strong>Webfone manual</strong></div>
                <button type="button" class="icon-button" data-webphone-close aria-label="Fechar webfone">x</button>
            </header>
            <form class="webphone-form" data-floating-webphone-form>
                <button type="button" class="phone-backspace" data-clear-phone aria-label="Limpar numero">&#9003;</button>
                <input type="hidden" name="campaign_id" value="<?= (int)$campaignId ?>">
                <input name="manual_phone" class="dial-display" placeholder="Pesquisar ou digitar numero" inputmode="tel" autocomplete="off" data-phone-search-input>
                <div class="webphone-tab-panel" data-tab-panel="recentes">
                    <div class="phone-subtabs">
                        <button type="button" class="active" data-phone-subtab="todas">Todas</button>
                        <button type="button" data-phone-subtab="recebidas">Recebidas</button>
                        <button type="button" data-phone-subtab="realizadas">Realizadas</button>
                        <button type="button" data-phone-subtab="perdidas">Perdidas</button>
                    </div>
                    <strong class="phone-history-day">Hoje</strong>
                    <div class="phone-subtab-panel active" data-subtab-panel="todas"><?php render_phone_history_items($recentCalls, 'Nenhuma ligacao recente.'); ?></div>
                    <div class="phone-subtab-panel" data-subtab-panel="recebidas"><?php render_phone_history_items($recentReceived, 'Nenhuma ligacao recebida registrada.'); ?></div>
                    <div class="phone-subtab-panel" data-subtab-panel="realizadas"><?php render_phone_history_items($recentMade, 'Nenhuma ligacao realizada.'); ?></div>
                    <div class="phone-subtab-panel" data-subtab-panel="perdidas"><?php render_phone_history_items($recentMissed, 'Nenhuma ligacao perdida.'); ?></div>
                </div>
                <div class="webphone-tab-panel" data-tab-panel="contatos">
                    <?php if (!$phoneContacts): ?>
                        <p class="phone-empty">Nenhum contato importado.</p>
                    <?php else: ?>
                        <?php foreach ($phoneContacts as $contact): ?>
                            <button type="button" class="phone-list-item" data-fill-phone="<?= h($contact['phone_e164']) ?>" data-phone-search="<?= h(strtolower(($contact['name'] ?? '') . ' ' . ($contact['phone_e164'] ?? '') . ' ' . ($contact['product'] ?? ''))) ?>">
                                <span><strong><?= h($contact['name'] ?: 'Sem nome') ?></strong><small><?= h($contact['phone_e164']) ?></small></span>
                                <em><?= h($contact['product'] ?: $contact['status']) ?></em>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="webphone-tab-panel phone-keypad-panel active" data-tab-panel="teclado">
                    <div class="dialpad" data-dialpad>
                        <?php foreach ([['1',''], ['2','ABC'], ['3','DEF'], ['4','GHI'], ['5','JKL'], ['6','MNO'], ['7','PQRS'], ['8','TUV'], ['9','WXYZ'], ['*',''], ['0','+'], ['#','']] as $key): ?>
                            <button type="button" data-digit="<?= h($key[0]) ?>"><strong><?= h($key[0]) ?></strong><small><?= h($key[1]) ?></small></button>
                        <?php endforeach; ?>
                    </div>
                    <button class="call-fab" type="submit" data-floating-call-button aria-label="Ligar manualmente">&#9742;</button>
                </div>
                <div class="webphone-tab-panel" data-tab-panel="monitorar">
                    <div class="phone-monitor <?= $activeCall ? 'online' : '' ?>" data-floating-monitor>
                        <span></span>
                        <strong data-floating-call-state><?= $activeCall ? 'Chamada ativa' : 'Sem chamada ativa' ?></strong>
                        <small data-floating-call-detail><?= h($activeCall['external_call_id'] ?? ($lastCall['external_call_id'] ?? 'Aguardando discagem')) ?></small>
                    </div>
                    <dl class="phone-monitor-details">
                        <dt>Registro</dt><dd data-floating-register>Desconectado</dd>
                        <dt>Status</dt><dd data-floating-status><?= h($activeCall['status'] ?? ($lastCall['status'] ?? 'pronto')) ?></dd>
                        <dt>Destino</dt><dd data-floating-destination><?= h($activeCall['destination_number'] ?? ($lastCall['destination_number'] ?? '-')) ?></dd>
                    </dl>
                    <audio data-floating-remote-audio autoplay></audio>
                </div>
            </form>
            <footer>
                <button type="button" data-phone-tab="recentes">Recentes</button>
                <button type="button" data-phone-tab="contatos">Contatos</button>
                <button type="button" class="active" data-phone-tab="teclado">Teclado</button>
                <button type="button" data-phone-tab="monitorar">Monitorar</button>
            </footer>
        </article>
    </section>
    <?php
}

function render_login(): void
{
    layout('login', function () { ?>
        <section class="login-card">
            <div class="brand login-brand"><span class="brand-mark"><img src="assets/img/logo-ligflow.png" alt=""></span><div><strong>Lig Flow</strong><small>Prospecção Inteligente</small></div></div>
            <form method="post" class="stack">
                <input type="hidden" name="action" value="login">
                <label>E-mail<input name="email" type="email" value="admin@consorciocall.local" required></label>
                <label>Senha<input name="password" type="password" value="admin123" required></label>
                <button class="button" type="submit">Entrar</button>
            </form>
            <div class="demo-users">
                <strong>Acessos demo</strong>
                <span>admin@consorciocall.local / admin123</span>
                <span>gestor@consorciocall.local / admin123</span>
                <span>supervisor@consorciocall.local / admin123</span>
                <span>consultor@consorciocall.local / admin123</span>
            </div>
        </section>
    <?php });
}

function render_dashboard(): void
{
        layout('dashboard', function () {
            [$clause, $params] = tenant_clause('c');
            $companyId = (int)current_user()['company_id'];
            $dashboardCostMicros = call_cost_sql('c');
            [$todayStartUtc, $todayEndUtc] = sao_paulo_utc_period_bounds('day');
            [$monthStartUtc] = sao_paulo_utc_period_bounds('month');
            $dashboardStats = one("
            SELECT
                COALESCE(SUM(CASE WHEN c.created_at >= ? AND c.created_at < ? THEN 1 ELSE 0 END), 0) chamadas_hoje,
                COALESCE(SUM(CASE WHEN c.created_at >= ? AND c.created_at < ? THEN c.billable_seconds ELSE 0 END), 0) segundos_hoje,
                COALESCE(SUM(CASE WHEN c.company_id = ? THEN c.billable_seconds ELSE 0 END), 0) segundos_mes_empresa,
                COALESCE(SUM(CASE WHEN c.created_at >= ? AND c.created_at < ? THEN {$dashboardCostMicros} ELSE 0 END), 0) gasto_hoje_micros
            FROM calls c
            WHERE {$clause} AND c.created_at >= ?
        ", array_merge([$todayStartUtc, $todayEndUtc, $todayStartUtc, $todayEndUtc, $companyId, $todayStartUtc, $todayEndUtc], $params, [$monthStartUtc])) ?: [];
        $usage = monthly_usage($companyId, (float)($dashboardStats['segundos_mes_empresa'] ?? 0));
        $cards = [
            'Chamadas hoje' => (int)($dashboardStats['chamadas_hoje'] ?? 0),
            'Minutos hoje' => number_format(((float)($dashboardStats['segundos_hoje'] ?? 0)) / 60, 0, ',', '.'),
            'Gasto hoje' => money(((int)($dashboardStats['gasto_hoje_micros'] ?? 0)) / 1000000),
            'Leads restantes' => one("SELECT COUNT(*) v FROM contacts c WHERE {$clause} AND c.status <> 'excluido' AND NOT EXISTS (SELECT 1 FROM calls co WHERE co.company_id = c.company_id AND co.contact_id = c.id)", $params)['v'],
        ];
        if (is_account_admin()) {
            $cards['Consultores ativos'] = one("SELECT COUNT(*) v FROM users c WHERE {$clause} AND c.role IN ('atendente','usuario_operacional') AND c.status <> 'Desconectado'", $params)['v'];
            $cards['Telefonia'] = active_call_provider_label();
        }
        ?>
        <section class="metric-grid">
            <?php foreach ($cards as $label => $value): ?>
                <article class="metric"><span><?= h($label) ?></span><strong><?= h((string)$value) ?></strong></article>
            <?php endforeach; ?>
        </section>
        <section class="grid two">
            <article class="panel">
                <h2>Resultados dos leads</h2>
                <?= table(rows("
                    SELECT ca.name campanha, COALESCE(cr.name, 'Sem resultado') resultado, COUNT(co.id) total
                    FROM campaigns ca
                    LEFT JOIN calls co ON co.campaign_id = ca.id
                    LEFT JOIN call_results cr ON cr.id = co.result_id
                    WHERE ca.company_id IN (SELECT company_id FROM users WHERE id = ?)
                    GROUP BY ca.id, cr.name
                    ORDER BY total DESC
                ", [current_user()['id']]), ['campanha', 'resultado', 'total']) ?>
            </article>
            <article class="panel">
                <h2>Proximos retornos</h2>
                <?php
                [$cbClause, $cbParams] = tenant_clause('cb');
                $callbacks = rows("
                    SELECT cb.id, cb.company_id, ct.name contato, ct.phone_e164 telefone,
                           cb.scheduled_at data, cb.priority prioridade, cb.status, cb.notes
                    FROM callbacks cb
                    JOIN contacts ct ON ct.id = cb.contact_id AND ct.company_id = cb.company_id
                    WHERE {$cbClause} AND lower(COALESCE(cb.status, 'pendente')) IN ('pendente','pending')
                    ORDER BY datetime(cb.scheduled_at)
                    LIMIT 8
                ", $cbParams);
                $callbackResultOptions = rows('SELECT id, company_id, name FROM call_results ORDER BY is_default DESC, id');
                $today = date('Y-m-d');
                ?>
                <?php if (!$callbacks): ?>
                    <p class="empty">Nenhum registro encontrado.</p>
                <?php else: ?>
                    <div class="table-wrap"><table><thead><tr><th>Contato</th><th>Data</th><th>Prioridade</th><th>Status</th><th>Acoes</th></tr></thead><tbody>
                    <?php foreach ($callbacks as $callback): ?>
                        <?php
                        $isToday = datetime_utc_display((string)$callback['data'], 'Y-m-d') === $today;
                        try {
                            $callbackDate = datetime_utc_display((string)$callback['data']);
                        } catch (Throwable) {
                            $callbackDate = (string)$callback['data'];
                        }
                        ?>
                        <tr<?= $isToday ? ' class="callback-today"' : '' ?>>
                            <td><?= h((string)$callback['contato']) ?></td>
                            <td><?= h($callbackDate) ?></td>
                            <td><?= h((string)$callback['prioridade']) ?></td>
                            <td><?= h((string)$callback['status']) ?></td>
                            <td><span class="callback-status-actions"><button class="callback-call-button" type="button" data-fill-phone="<?= h((string)$callback['telefone']) ?>" title="Ligar para este contato" aria-label="Ligar para <?= h((string)$callback['contato']) ?>">&#9742;</button><button class="mini-link" type="button" data-open-callback="<?= (int)$callback['id'] ?>">Gerenciar</button></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table></div>
                    <?php foreach ($callbacks as $callback): ?>
                        <?php
                        $callbackPhone = (string)$callback['telefone'];
                        $callbackPhoneNormalized = normalize_phone($callbackPhone);
                        $callbackWhatsappLink = $callbackPhoneNormalized ? 'https://wa.me/' . ltrim($callbackPhoneNormalized, '+') : '';
                        ?>
                        <section class="call-modal-backdrop is-hidden" data-callback-modal="<?= (int)$callback['id'] ?>">
                            <article class="call-modal callback-manage-modal">
                                <header>
                                    <div>
                                        <span class="modal-kicker">Proximo retorno</span>
                                        <h2><?= h((string)$callback['contato']) ?></h2>
                                        <p>
                                            <?php if ($callbackWhatsappLink): ?>
                                                <a class="whatsapp-phone-link" href="<?= h($callbackWhatsappLink) ?>" target="_blank" rel="noopener" title="Conversar pelo WhatsApp"><?= h($callbackPhone) ?></a>
                                            <?php else: ?>
                                                <?= h($callbackPhone) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <button type="button" class="icon-button" data-callback-modal-close aria-label="Fechar modal">x</button>
                                </header>
                                <form method="post" class="stack">
                                    <input type="hidden" name="action" value="update_callback">
                                    <input type="hidden" name="callback_id" value="<?= (int)$callback['id'] ?>">
                                    <label>Anotacoes<textarea name="callback_notes" rows="4"><?= h((string)$callback['notes']) ?></textarea></label>
                                    <label>Status<select name="callback_status">
                                        <option value="pendente" selected>Continuar agendamento</option>
                                        <option value="atendido">Atendido</option>
                                        <?php foreach ($callbackResultOptions as $resultOption): ?>
                                            <?php if ($resultOption['company_id'] === null || (int)$resultOption['company_id'] === (int)$callback['company_id']): ?>
                                                <option value="resultado:<?= (int)$resultOption['id'] ?>"><?= h((string)$resultOption['name']) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select></label>
                                    <label>Nova data de retorno<input name="callback_at" type="datetime-local" value="<?= h(datetime_local((string)$callback['data'])) ?>"></label>
                                    <label>Prioridade<select name="callback_priority"><option <?= $callback['prioridade'] === 'normal' ? 'selected' : '' ?>>normal</option><option <?= $callback['prioridade'] === 'alta' ? 'selected' : '' ?>>alta</option><option <?= $callback['prioridade'] === 'urgente' ? 'selected' : '' ?>>urgente</option></select></label>
                                    <button class="button" type="submit">Salvar retorno</button>
                                </form>
                            </article>
                        </section>
                    <?php endforeach; ?>
                <?php endif; ?>
            </article>
        </section>
        <section class="grid two">
            <article class="panel">
                <h2>Ligações por hora hoje</h2>
                <?= table(rows("SELECT strftime('%H:00', ligflow_local_datetime(c.created_at)) hora, COUNT(*) ligacoes, ROUND(SUM(c.billable_seconds) / 60.0, 1) minutos, printf('%.2f', SUM({$dashboardCostMicros}) / 1000000.0) gasto FROM calls c WHERE {$clause} AND c.created_at >= ? AND c.created_at < ? GROUP BY hora ORDER BY hora", array_merge($params, [$todayStartUtc, $todayEndUtc])), ['hora', 'ligacoes', 'minutos', 'gasto']) ?>
            </article>
            <article class="panel">
                <h2><?= is_platform_admin() ? 'Configuracao tecnica' : 'Telefonia' ?></h2>
                <?php if (is_platform_admin()): ?>
                    <dl>
                        <dt>Modo</dt><dd><?= h(voip_status_label()) ?></dd>
                        <dt>Origem</dt><dd><?= h(env_value('NVOIP_ORIGIN', 'Usando numero da campanha')) ?></dd>
                        <dt>Custo interno/min</dt><dd><?= h(money(price_per_minute())) ?></dd>
                        <dt>Gravacao</dt><dd><?= env_value('NVOIP_RECORDING_ENABLED', '1') === '1' ? 'Ativa' : 'Inativa' ?></dd>
                    </dl>
                    <p class="hint">A telefonia e administrada em Integracoes, sem alterar o fluxo ja validado de chamadas.</p>
                <?php else: ?>
                    <dl>
                        <dt>Status</dt><dd><?= nvoip_enabled() ? 'Disponivel' : 'Indisponivel' ?></dd>
                        <dt>Minutos hoje</dt><dd><?= h((string)$cards['Minutos hoje']) ?></dd>
                        <dt>Chamadas hoje</dt><dd><?= h((string)$cards['Chamadas hoje']) ?></dd>
                        <dt>Gravacao</dt><dd>Conforme plano</dd>
                    </dl>
                    <p class="hint">O servico de ligacoes e gerenciado pela plataforma.</p>
                <?php endif; ?>
            </article>
        </section>
    <?php });
}

function table(array $rows, array $columns, ?string $timezone = null): string
{
    $dateColumns = ['created_at', 'updated_at', 'started_at', 'ringing_at', 'answered_at', 'connected_at', 'ended_at', 'finalized_at', 'scheduled_at', 'completed_at', 'approved_at', 'expires_at', 'starts_at', 'ends_at', 'renews_at', 'provisioned_at', 'last_event_at', 'next_started_at'];
    $labels = [
        'acoes' => 'Ações',
        'duracao_min' => 'Duração',
        'gravacao' => 'Gravação',
        'ligacoes' => 'Ligações',
        'concluidas' => 'Concluídas',
        'usuarios' => 'Usuários',
        'identificacao' => 'Identificacao',
        'autenticacao' => 'Autenticação',
        'integracao' => 'Integração',
        'periodo' => 'Período',
        'ate' => 'Até',
    ];
    ob_start();
    if (!$rows) {
        echo '<p class="empty">Nenhum registro encontrado.</p>';
        return ob_get_clean();
    }
    echo '<div class="table-wrap"><table><thead><tr>';
    foreach ($columns as $column) {
        echo '<th>' . h($labels[$column] ?? ucfirst(str_replace('_', ' ', $column))) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($columns as $column) {
            $value = (string)($row[$column] ?? '');
            if (in_array($column, $dateColumns, true) || ($column === 'data' && preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?/', $value))) {
                $value = datetime_utc_display($value, 'd/m/Y H:i:s', $timezone);
            }
            echo '<td>' . h($value) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    return ob_get_clean();
}

function render_plans(): void
{
    layout('plans', function () {
        $editPlanId = (int)($_GET['edit_plan'] ?? 0);
        $editPlan = $editPlanId ? one("SELECT * FROM plans WHERE id=? AND status <> 'Removido'", [$editPlanId]) : null;
        $plans = rows("SELECT * FROM plans WHERE status <> 'Removido' ORDER BY status,id DESC");
        $showCreateModal = isset($_GET['new_plan']);
        $editing = (bool)$editPlan;
        $field = fn(string $key, mixed $default = '') => $editPlan[$key] ?? $default;
        ?>
        <section class="panel" id="campaign-call-logs">
            <div class="section-head">
                <div>
                    <h2>Planos cadastrados</h2>
                    <p>Administre valores, minutos incluidos e limites comerciais.</p>
                </div>
                <a class="button" href="?page=plans&new_plan=1">Criar novo plano</a>
            </div>
                <?php if (!$plans): ?>
                    <p class="empty">Nenhum plano encontrado.</p>
                <?php else: ?>
                    <div class="table-wrap"><table>
                        <thead><tr><th>Plano</th><th>Assinatura</th><th>Credito/ciclo</th><th>Tarifa/min</th><th>Periodo</th><th>Pagamento</th><th>Minutos</th><th>Status</th><th>Acoes</th></tr></thead>
                        <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <?php $linkedUserCount = plan_linked_user_count($plan); ?>
                            <tr>
                                <td><?= h($plan['name']) ?></td>
                                <td><?= h(money((float)$plan['monthly_price'])) ?></td>
                                <td><?= $plan['telephony_credit_micros'] === null ? '<span class="muted">Nao configurado</span>' : h(billing_micros_to_brl((int)$plan['telephony_credit_micros'])) ?></td>
                                <td><?= $plan['telephony_rate_micros'] === null ? '<span class="muted">Nao configurada</span>' : h(billing_micros_to_brl((int)$plan['telephony_rate_micros'])) . '/min' ?></td>
                                <td><?= h($plan['billing_period']) ?></td>
                                <td><?= h($plan['payment_type']) ?></td>
                                <td><?= h((string)$plan['included_minutes']) ?></td>
                                <td><?= h($plan['status']) ?></td>
                                <td class="actions">
                                    <a class="mini-link" href="?page=plans&edit_plan=<?= (int)$plan['id'] ?>">Editar</a>
                                    <?php if ($linkedUserCount === 0): ?>
                                        <form method="post" class="inline" onsubmit="return confirm('Remover este plano sem usuários vinculados?');">
                                            <input type="hidden" name="action" value="delete_plan">
                                            <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                                            <button class="mini-link danger-link" type="submit">Remover</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                <?php endif; ?>
        </section>
        <?php if ($showCreateModal || $editing): ?>
            <section class="call-modal-backdrop">
                <article class="call-modal plan-modal">
                    <header>
                        <div>
                            <span class="modal-kicker"><?= $editing ? 'Editar plano' : 'Novo plano' ?></span>
                            <h2><?= $editing ? h((string)$field('name', 'Editar plano')) : 'Novo plano' ?></h2>
                            <p><?= $editing ? 'Atualize valores, minutos e limites deste plano.' : 'Cadastre um plano para vincular aos clientes.' ?></p>
                        </div>
                        <a class="icon-button" href="?page=plans" aria-label="Fechar modal">x</a>
                    </header>
                    <form class="form-grid" method="post">
                        <input type="hidden" name="action" value="<?= $editing ? 'update_plan' : 'save_plan' ?>">
                        <?php if ($editing): ?><input type="hidden" name="plan_id" value="<?= (int)$editPlan['id'] ?>"><?php endif; ?>
                        <label>Nome do plano<input name="name" value="<?= h((string)$field('name')) ?>" required></label>
                        <label>Status<select name="status"><?php foreach (['Ativo','Inativo'] as $status): ?><option <?= (string)$field('status', 'Ativo') === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select></label>
                        <label>Valor mensal<input name="monthly_price" type="number" step="0.01" value="<?= h((string)$field('monthly_price', '0')) ?>"></label>
                        <label>Taxa de implantacao<input name="setup_fee" type="number" step="0.01" value="<?= h((string)$field('setup_fee', '0')) ?>"></label>
                        <label>Periodo<select name="billing_period"><?php foreach (['Mensal','Trimestral','Semestral','Anual','Teste'] as $period): ?><option <?= (string)$field('billing_period', 'Mensal') === $period ? 'selected' : '' ?>><?= h($period) ?></option><?php endforeach; ?></select></label>
                        <label>Tipo de pagamento<select name="payment_type"><?php foreach (['Pix','Cartao','Boleto','Pix/Cartao','Boleto/Pix','Manual'] as $payment): ?><option <?= (string)$field('payment_type', 'Pix') === $payment ? 'selected' : '' ?>><?= h($payment) ?></option><?php endforeach; ?></select></label>
                        <label>Minutos do plano<input name="included_minutes" type="number" min="0" value="<?= h((string)$field('included_minutes', '200')) ?>"></label>
                        <label>Credito de telefonia por ciclo<input name="telephony_credit_amount" type="number" min="0" step="0.000001" required value="<?= h($editing && $field('telephony_credit_micros') !== null ? billing_micros_to_decimal((int)$field('telephony_credit_micros')) : '') ?>"></label>
                        <label>Tarifa de telefonia por minuto<input name="telephony_rate_per_minute" type="number" min="0" step="0.000001" required value="<?= h($editing && $field('telephony_rate_micros') !== null ? billing_micros_to_decimal((int)$field('telephony_rate_micros')) : '') ?>"></label>
                        <label>Limite usuarios<input name="max_users" type="number" value="<?= h((string)$field('max_users', '1')) ?>"></label>
                        <label>Limite consultores<input name="max_consultants" type="number" value="<?= h((string)$field('max_consultants', '1')) ?>"></label>
                        <label>Limite listas<input name="max_lists" type="number" value="<?= h((string)$field('max_lists', '10')) ?>"></label>
                        <label>Limite contatos<input name="max_contacts" type="number" value="<?= h((string)$field('max_contacts', '1000')) ?>"></label>
                        <label class="wide">Descricao<textarea name="description" rows="4"><?= h((string)$field('description')) ?></textarea></label>
                        <button class="button"><?= $editing ? 'Salvar plano' : 'Criar plano' ?></button>
                    </form>
                </article>
            </section>
        <?php endif; ?>
    <?php });
}

function render_companies(): void
{
    layout('companies', function () {
        $plans = rows("SELECT * FROM plans WHERE status = 'Ativo' ORDER BY id");
        $planOption = function (array $plan, string $selected = ''): void {
            ?>
            <option
                value="<?= h($plan['name']) ?>"
                data-minutes="<?= h((string)$plan['included_minutes']) ?>"
                data-max-users="<?= h((string)$plan['max_users']) ?>"
                data-max-consultants="<?= h((string)$plan['max_consultants']) ?>"
                data-max-lists="<?= h((string)$plan['max_lists']) ?>"
                data-max-contacts="<?= h((string)$plan['max_contacts']) ?>"
                data-price-minute="<?= h((string)$plan['commercial_price_per_minute']) ?>"
                data-monthly-price="<?= h((string)$plan['monthly_price']) ?>"
                data-period="<?= h((string)$plan['billing_period']) ?>"
                data-payment="<?= h((string)$plan['payment_type']) ?>"
                <?= $selected === $plan['name'] ? 'selected' : '' ?>
            ><?= h($plan['name'] . ' - ' . (int)$plan['included_minutes'] . ' min - ' . money((float)$plan['monthly_price'])) ?></option>
            <?php
        };
        $integrations = rows("SELECT id, integration_name, provider, mode FROM integration_settings ORDER BY integration_name, provider");
        $editClientId = (int)($_GET['edit_client'] ?? 0);
        $editClient = $editClientId ? one("SELECT c.*, COALESCE(s.plan_name, c.plan) AS plan_name, s.renews_at, s.included_minutes, s.max_users sub_max_users, s.max_consultants, s.max_lists, s.max_contacts, s.commercial_price_per_minute, s.status subscription_status FROM companies c LEFT JOIN subscriptions s ON s.company_id = c.id WHERE c.id = ? AND c.status <> 'Removida'", [$editClientId]) : null;
        $showCreateModal = isset($_GET['new_client']);
        $clients = rows("SELECT c.id, c.trade_name cliente, c.cnpj documento, c.status, COALESCE(s.plan_name, c.plan) plano, c.max_users usuarios, c.max_agents consultores, c.monthly_minutes_limit minutos,
            (EXISTS(SELECT 1 FROM users u WHERE u.company_id=c.id)
                OR EXISTS(SELECT 1 FROM subscriptions sx WHERE sx.company_id=c.id)
                OR EXISTS(SELECT 1 FROM contact_lists l WHERE l.company_id=c.id)
                OR EXISTS(SELECT 1 FROM contacts ct WHERE ct.company_id=c.id)
                OR EXISTS(SELECT 1 FROM campaigns ca WHERE ca.company_id=c.id)
                OR EXISTS(SELECT 1 FROM calls co WHERE co.company_id=c.id)
                OR EXISTS(SELECT 1 FROM callbacks cb WHERE cb.company_id=c.id)
                OR EXISTS(SELECT 1 FROM payments p WHERE p.company_id=c.id)
                OR EXISTS(SELECT 1 FROM telephony_ledger tl WHERE tl.company_id=c.id)) has_saved_data
            FROM companies c LEFT JOIN subscriptions s ON s.company_id = c.id
            WHERE c.status <> 'Removida' ORDER BY c.id DESC");
        ?>
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Clientes cadastrados</h2>
                    <p>Gerencie os clientes, planos e limites de uso da plataforma.</p>
                </div>
                <a class="button" href="?page=companies&new_client=1">Criar novo cliente</a>
            </div>
            <?php if (!$clients): ?>
                <p class="empty">Nenhum cliente cadastrado.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Cliente</th><th>Documento</th><th>Status</th><th>Plano</th><th>Usuarios</th><th>Consultores</th><th>Minutos</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?= h($client['cliente']) ?></td>
                                <td><?= h($client['documento']) ?></td>
                                <td><?= h($client['status']) ?></td>
                                <td><?= h($client['plano']) ?></td>
                                <td><?= h((string)$client['usuarios']) ?></td>
                                <td><?= h((string)$client['consultores']) ?></td>
                                <td><?= h((string)$client['minutos']) ?></td>
                                <td class="actions">
                                    <a class="mini-link" href="?page=companies&edit_client=<?= (int)$client['id'] ?>">Editar</a>
                                    <?php $removeClientWarning = (int)$client['has_saved_data'] === 1
                                        ? 'Este cliente contem dados no sistema. Ele sera apenas ocultado da listagem e todo o historico sera preservado. Deseja continuar?'
                                        : 'Remover este cliente da listagem?'; ?>
                                    <form method="post" class="inline" onsubmit="return confirm(<?= h(json_encode($removeClientWarning, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>);">
                                        <input type="hidden" name="action" value="remove_client">
                                        <input type="hidden" name="client_id" value="<?= (int)$client['id'] ?>">
                                        <button class="mini-link danger-link" type="submit">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <?php if ($showCreateModal): ?>
            <section class="call-modal-backdrop">
                <article class="call-modal client-modal">
                    <header>
                        <div>
                            <span class="modal-kicker">Novo acesso</span>
                            <h2>Novo cliente</h2>
                            <p>Crie o cliente com acesso principal e plano inicial.</p>
                        </div>
                        <a class="icon-button" href="?page=companies" aria-label="Fechar modal">x</a>
                    </header>
                    <form class="form-grid" method="post">
                        <input type="hidden" name="action" value="create_client_account">
                        <input type="hidden" name="client_type" value="Consultor individual">
                        <input type="hidden" name="access_status" value="Ativo">
                        <input type="hidden" name="max_users" data-plan-field="maxUsers" value="1">
                        <input type="hidden" name="max_consultants" data-plan-field="maxConsultants" value="1">
                        <input type="hidden" name="max_lists" data-plan-field="maxLists" value="10">
                        <input type="hidden" name="max_contacts" data-plan-field="maxContacts" value="1000">
                        <input type="hidden" name="commercial_price_per_minute" data-plan-field="priceMinute" value="0.35">
                        <input type="hidden" name="subscription_status" value="Ativa">
                        <input type="hidden" name="integration_id" value="">
                        <input type="hidden" name="origin_rule" value="">
                        <input type="hidden" name="origin_number" value="">
                        <input type="hidden" name="max_simultaneous_calls" value="1">
                        <input type="hidden" name="internal_cost_per_minute" value="0">
                        <input type="hidden" name="calls_enabled" value="1">
                        <input type="hidden" name="recording_enabled" value="1">
                        <input type="hidden" name="consultant_code" value="consultor-principal">
                        <input type="hidden" name="consultant_goal" value="0">
                        <h3>Dados principais</h3>
                        <label>Nome do cliente<input name="client_name" required></label>
                        <label>Responsavel<input name="responsible_name" required></label>
                        <label>CPF ou CNPJ<input name="document"></label>
                        <label>WhatsApp<input name="whatsapp" data-phone-mask inputmode="tel"></label>
                        <h3>Acesso principal</h3>
                        <label>Nome do usuario<input name="user_name" placeholder="Se vazio, usa o responsavel"></label>
                        <label>E-mail de login<input name="login_email" type="email" required></label>
                        <label>Senha inicial<input name="password" value="admin123"></label>
                        <h3>Plano</h3>
                        <label>Plano<select name="plan_name" data-plan-select><?php foreach ($plans as $plan): ?><?php $planOption($plan); ?><?php endforeach; ?></select></label>
                        <label>Valor do plano<input data-plan-display="monthly_price" value="" readonly></label>
                        <label>Periodo<input data-plan-display="period" value="" readonly></label>
                        <label>Pagamento<input data-plan-display="payment" value="" readonly></label>
                        <label>Vencimento<input name="renews_at" type="date"></label>
                        <label>Minutos incluidos<input name="included_minutes" data-plan-field="minutes" type="number" value="200"></label>
                        <label>Nome no discador<input name="consultant_display_name" placeholder="Opcional"></label>
                        <button class="button">Criar cliente</button>
                    </form>
                </article>
            </section>
        <?php endif; ?>
        <?php if ($editClient): ?>
            <section class="call-modal-backdrop">
                <article class="call-modal client-modal">
                    <header>
                        <div>
                            <span class="modal-kicker">Editar cliente</span>
                            <h2><?= h($editClient['trade_name']) ?></h2>
                            <p>Atualize dados cadastrais, plano e limites.</p>
                        </div>
                        <a class="icon-button" href="?page=companies" aria-label="Fechar modal">x</a>
                    </header>
                    <form class="form-grid" method="post">
                        <input type="hidden" name="action" value="update_client_account">
                        <input type="hidden" name="client_id" value="<?= (int)$editClient['id'] ?>">
                        <input type="hidden" name="legal_name" value="<?= h($editClient['legal_name']) ?>">
                        <input type="hidden" name="max_lists" data-plan-field="maxLists" value="<?= h((string)($editClient['max_lists'] ?: 10)) ?>">
                        <input type="hidden" name="max_contacts" data-plan-field="maxContacts" value="<?= h((string)($editClient['max_contacts'] ?: 1000)) ?>">
                        <input type="hidden" name="max_channels" value="<?= h((string)($editClient['max_channels'] ?: 1)) ?>">
                        <input type="hidden" name="commercial_price_per_minute" data-plan-field="priceMinute" value="<?= h((string)($editClient['commercial_price_per_minute'] ?: 0.35)) ?>">
                        <input type="hidden" name="subscription_status" value="<?= h((string)($editClient['subscription_status'] ?: 'Ativa')) ?>">
                        <input type="hidden" name="call_window" value="<?= h((string)($editClient['call_window'] ?: '08:00-18:00')) ?>">
                        <label>Nome do cliente<input name="trade_name" value="<?= h($editClient['trade_name']) ?>" required></label>
                        <label>CPF ou CNPJ<input name="document" value="<?= h($editClient['cnpj']) ?>"></label>
                        <label>E-mail<input name="email" type="email" value="<?= h($editClient['email']) ?>"></label>
                        <label>WhatsApp/telefone<input name="phone" value="<?= h($editClient['phone']) ?>" data-phone-mask inputmode="tel"></label>
                        <label>Status<select name="status"><?php foreach (['Ativa','Em implantacao','Suspensa','Inadimplente','Cancelada','Bloqueada'] as $status): ?><option <?= $editClient['status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select></label>
                        <?php $selectedPlan = (string)($editClient['plan_name'] ?? $editClient['plan'] ?? 'MVP'); ?>
                        <label>Plano<select name="plan_name" data-plan-select data-plan-preserve-current><?php foreach ($plans as $plan): ?><?php $planOption($plan, $selectedPlan); ?><?php endforeach; ?></select></label>
                        <label>Valor do plano<input data-plan-display="monthly_price" value="" readonly></label>
                        <label>Periodo<input data-plan-display="period" value="" readonly></label>
                        <label>Pagamento<input data-plan-display="payment" value="" readonly></label>
                        <label>Vencimento<input name="renews_at" type="date" value="<?= h((string)$editClient['renews_at']) ?>"></label>
                        <label>Minutos incluidos<input name="included_minutes" data-plan-field="minutes" type="number" value="<?= h((string)($editClient['included_minutes'] ?: $editClient['monthly_minutes_limit'])) ?>"></label>
                        <label>Limite usuarios<input name="max_users" data-plan-field="maxUsers" type="number" value="<?= h((string)($editClient['sub_max_users'] ?: $editClient['max_users'])) ?>"></label>
                        <label>Limite consultores<input name="max_consultants" data-plan-field="maxConsultants" type="number" value="<?= h((string)($editClient['max_consultants'] ?: $editClient['max_agents'])) ?>"></label>
                        <button class="button">Salvar cliente</button>
                    </form>
                </article>
            </section>
        <?php endif; ?>
    <?php });
}

function render_users(): void
{
    layout('users', function () {
        [$teamClause, $teamParams] = tenant_clause();
        [$userClause, $userParams] = tenant_clause('u');
        $companies = rows('SELECT id, trade_name FROM companies ORDER BY trade_name');
        $teams = rows("SELECT id, name FROM teams WHERE {$teamClause} ORDER BY name", $teamParams);
        $isPlatformAdmin = in_array(current_user()['role'], ['admin_geral', 'admin_plataforma'], true);
        $profileCompanyId = $isPlatformAdmin && isset($_GET['company_id']) ? (int)$_GET['company_id'] : (int)(current_user()['company_id'] ?? 0);
        if ($profileCompanyId <= 0) {
            $profileCompanyId = (int)(current_user()['company_id'] ?? 0);
        }
        $accessProfiles = rows('SELECT ap.*, (SELECT COUNT(*) FROM users u WHERE u.access_profile_id = ap.id) linked_users FROM access_profiles ap WHERE ap.company_id = ? ORDER BY ap.role_key, ap.name', [$profileCompanyId]);
        $editProfileId = (int)($_GET['profile_id'] ?? 0);
        $editProfile = $editProfileId ? one('SELECT * FROM access_profiles WHERE id = ? AND company_id = ?', [$editProfileId, $profileCompanyId]) : null;
        $showProfileModal = $isPlatformAdmin && (isset($_GET['new_profile']) || $editProfile);
        $editUserId = (int)($_GET['edit_user'] ?? 0);
        $editUser = $editUserId ? one("SELECT u.*, cp.display_name consultant_display_name, cp.internal_code consultant_code, cp.status consultant_status, cp.goal consultant_goal, axe.extension asterisk_extension, axe.status asterisk_extension_status, axe.lifecycle_status asterisk_lifecycle_status, axe.provisioning_status asterisk_provisioning_status FROM users u LEFT JOIN consultant_profiles cp ON cp.user_id = u.id LEFT JOIN asterisk_user_extensions axe ON axe.user_id = u.id AND axe.company_id = u.company_id AND axe.asterisk_server_id = 1 AND axe.status = 'Ativo' WHERE u.id = ? AND u.deleted_at IS NULL AND " . ($isPlatformAdmin ? '1=1' : 'u.company_id = ?'), $isPlatformAdmin ? [$editUserId] : [$editUserId, current_user()['company_id']]) : null;
        $userProfileCompanyId = $editUser ? (int)$editUser['company_id'] : $profileCompanyId;
        $userProfiles = rows('SELECT * FROM access_profiles WHERE company_id = ? ORDER BY role_key, name', [$userProfileCompanyId]);
        $showCreateModal = $isPlatformAdmin && isset($_GET['new_user']);
        $accessRows = rows("SELECT u.id, u.company_id, u.name nome, u.email, u.role perfil, COALESCE(ap.name, '') perfil_acesso, COALESCE(t.name, '-') equipe, u.status, u.extension identificacao, COALESCE(axe.extension, '-') asterisk_extension, COALESCE(axe.lifecycle_status, axe.status, 'Sem ramal') asterisk_extension_status FROM users u LEFT JOIN teams t ON t.id = u.team_id LEFT JOIN access_profiles ap ON ap.id = u.access_profile_id LEFT JOIN asterisk_user_extensions axe ON axe.user_id = u.id AND axe.company_id = u.company_id AND axe.asterisk_server_id = 1 AND axe.status = 'Ativo' WHERE {$userClause} AND u.deleted_at IS NULL ORDER BY u.id DESC", $userParams);
        foreach ($accessRows as &$accessRow) {
            $accessRow['can_remove'] = user_access_can_be_removed((int)$accessRow['id'], (int)current_user()['company_id']);
            $accessRow['has_saved_data'] = user_access_blocking_relation_counts((int)$accessRow['id'], (int)$accessRow['company_id']) !== [];
        }
        unset($accessRow);
        ?>
        <?php if ($showProfileModal): ?>
            <?php
            $profileModules = $editProfile ? modules_from_json((string)$editProfile['modules_json']) : default_role_modules('usuario_operacional');
            ?>
            <section class="call-modal-backdrop">
                <article class="call-modal user-modal">
                    <header>
                        <div>
                            <span class="modal-kicker"><?= $editProfile ? 'Editar perfil' : 'Novo perfil' ?></span>
                            <h2><?= h($editProfile['name'] ?? 'Perfil de usuario') ?></h2>
                            <p>Defina quais modulos este perfil libera.</p>
                        </div>
                        <a class="icon-button" href="?page=users" aria-label="Fechar modal">x</a>
                    </header>
                    <form class="form-grid" method="post">
                        <input type="hidden" name="action" value="save_access_profile">
                        <input type="hidden" name="profile_id" value="<?= (int)($editProfile['id'] ?? 0) ?>">
                        <label>Nome do perfil<input name="name" value="<?= h((string)($editProfile['name'] ?? '')) ?>" required placeholder="Ex: Consultor padrao"></label>
                        <label>Base do perfil<select name="role_key">
                            <?php foreach (['cliente_admin', 'usuario_operacional', 'supervisor', 'atendente'] as $roleOption): ?>
                                <option value="<?= h($roleOption) ?>" <?= (string)($editProfile['role_key'] ?? 'usuario_operacional') === $roleOption ? 'selected' : '' ?>><?= h(role_label($roleOption)) ?></option>
                            <?php endforeach; ?>
                        </select></label>
                        <h3 class="wide">Modulos liberados</h3>
                        <?= module_checkboxes($profileModules) ?>
                        <button class="button"><?= $editProfile ? 'Salvar perfil' : 'Criar perfil' ?></button>
                    </form>
                </article>
            </section>
        <?php endif; ?>
        <?php if ($editUser): ?>
            <section class="call-modal-backdrop">
                <article class="call-modal user-modal">
                    <header>
                        <div>
                            <span class="modal-kicker">Editar acesso</span>
                            <h2><?= h($editUser['name']) ?></h2>
                            <p><?= h($editUser['email']) ?></p>
                        </div>
                        <a class="icon-button" href="?page=users" aria-label="Fechar modal">x</a>
                    </header>
                <form class="form-grid" method="post">
                    <input type="hidden" name="action" value="update_user_access">
                    <input type="hidden" name="user_id" value="<?= (int)$editUser['id'] ?>">
                    <input type="hidden" name="team_id" value="<?= h((string)$editUser['team_id']) ?>">
                    <input type="hidden" name="work_hours" value="<?= h((string)($editUser['work_hours'] ?: '08:00-18:00')) ?>">
                    <input type="hidden" name="consultant_status" value="<?= h((string)($editUser['consultant_status'] ?: 'Ativo')) ?>">
                    <input type="hidden" name="consultant_goal" value="<?= h((string)($editUser['consultant_goal'] ?? 0)) ?>">
                    <?php if ($isPlatformAdmin): ?>
                        <label>Cliente<select name="company_id"><?php foreach ($companies as $c): ?><option value="<?= $c['id'] ?>" <?= (int)$editUser['company_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['trade_name']) ?></option><?php endforeach; ?></select></label>
                    <?php endif; ?>
                    <label>Nome<input name="name" value="<?= h($editUser['name']) ?>" required></label>
                    <label>E-mail<input type="email" name="email" value="<?= h($editUser['email']) ?>" required></label>
                    <label>Nova senha<input name="password" placeholder="Deixe em branco para manter"></label>
                    <?php if ($isPlatformAdmin): ?>
                        <label>Perfil<select name="access_profile_id">
                            <option value="">Manter perfil atual: <?= h(role_label((string)$editUser['role'])) ?></option>
                            <?php foreach ($userProfiles as $profile): ?>
                                <option value="<?= (int)$profile['id'] ?>" <?= (int)($editUser['access_profile_id'] ?? 0) === (int)$profile['id'] ? 'selected' : '' ?>><?= h($profile['name']) ?></option>
                            <?php endforeach; ?>
                        </select></label>
                    <?php endif; ?>
                    <label>Telefone<input name="phone" value="<?= h($editUser['phone']) ?>" data-phone-mask inputmode="tel" placeholder="Ex: (41) 99631-0725"></label>
                    <label>Status<select name="status"><?php foreach (['Ativo','Disponivel','Em pausa','Desconectado','Bloqueado'] as $status): ?><option <?= $editUser['status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select></label>
                    <label>Nome de consultor<input name="consultant_display_name" value="<?= h($editUser['consultant_display_name'] ?: $editUser['name']) ?>"></label>
                    <label>Identificacao<input name="extension" value="<?= h($editUser['extension']) ?>" placeholder="Opcional"></label>
                    <label>Ramal Asterisk<input name="asterisk_extension" value="<?= h((string)($editUser['asterisk_extension'] ?? '')) ?>" inputmode="numeric" pattern="[0-9]*" placeholder="Ex: 1003"><small><?= h((string)($editUser['asterisk_lifecycle_status'] ?? $editUser['asterisk_extension_status'] ?? 'Sem ramal')) ?><?= !empty($editUser['asterisk_provisioning_status']) ? ' - ' . h((string)$editUser['asterisk_provisioning_status']) : '' ?></small></label>
                    <?php if ($isPlatformAdmin): ?>
                        <h3 class="wide">Modulos deste usuario</h3>
                        <?= module_checkboxes(modules_for_user($editUser)) ?>
                    <?php endif; ?>
                    <input type="hidden" name="consultant_code" value="<?= h($editUser['consultant_code'] ?: $editUser['extension']) ?>">
                    <button class="button">Salvar acesso</button>
                </form>
                </article>
            </section>
        <?php endif; ?>
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Acessos</h2>
                    <p>Gerencie os usuarios que entram no sistema.</p>
                </div>
                <?php if ($isPlatformAdmin): ?>
                    <a class="button" href="?page=users&new_user=1">Criar novo acesso</a>
                <?php endif; ?>
            </div>
            <?php if (!$accessRows): ?>
                <p class="empty">Nenhum acesso cadastrado.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Equipe</th><th>Status</th><th>Identificacao</th><th>Ramal Asterisk</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($accessRows as $access): ?>
                            <tr>
                                <td><?= h($access['nome']) ?></td>
                                <td><?= h($access['email']) ?></td>
                                <td><?= h($access['perfil_acesso'] ?: role_label($access['perfil'])) ?></td>
                                <td><?= h($access['equipe']) ?></td>
                                <td><?= h($access['status']) ?></td>
                                <td><?= h($access['identificacao']) ?></td>
                                <td><?= h($access['asterisk_extension']) ?><small><?= h($access['asterisk_extension_status']) ?></small></td>
                                <td class="actions">
                                    <a class="mini-link" href="?page=users&edit_user=<?= (int)$access['id'] ?>">Editar</a>
                                    <?php if (!empty($access['can_remove'])): ?>
                                        <form method="post" class="inline" onsubmit="return confirm('<?= !empty($access['has_saved_data']) ? 'Este usuário possui dados gravados. O acesso será ocultado e bloqueado, mas o histórico será preservado. Deseja continuar?' : 'Este usuário não possui dados gravados e será removido definitivamente. Deseja continuar?' ?>');">
                                            <input type="hidden" name="action" value="delete_user_access">
                                            <input type="hidden" name="user_id" value="<?= (int)$access['id'] ?>">
                                            <button class="mini-link danger-link" type="submit">Remover</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <?php if ($showCreateModal): ?>
            <section class="call-modal-backdrop">
                <article class="call-modal user-modal">
                    <header>
                        <div>
                            <span class="modal-kicker">Novo acesso</span>
                            <h2>Novo acesso</h2>
                            <p>Crie apenas quem precisa entrar no sistema.</p>
                        </div>
                        <a class="icon-button" href="?page=users" aria-label="Fechar modal">x</a>
                    </header>
                    <form class="form-grid" method="post">
                        <input type="hidden" name="action" value="save_user">
                        <input type="hidden" name="team_id" value="">
                        <input type="hidden" name="status" value="Ativo">
                        <input type="hidden" name="work_hours" value="08:00-18:00">
                        <input type="hidden" name="extension" value="">
                        <p class="hint wide">Para consultores extras, use perfil Usuario operacional.</p>
                        <?php if ($isPlatformAdmin): ?>
                            <label>Cliente<select name="company_id"><?php foreach ($companies as $c): ?><option value="<?= $c['id'] ?>"><?= h($c['trade_name']) ?></option><?php endforeach; ?></select></label>
                        <?php endif; ?>
                        <label>Nome<input name="name" required></label>
                        <label>E-mail<input type="email" name="email" required></label>
                        <label>Senha inicial<input name="password" value="admin123"></label>
                        <label>Perfil<select name="access_profile_id">
                            <?php if (!$accessProfiles): ?><option value="">Usuario operacional</option><?php endif; ?>
                            <?php foreach ($accessProfiles as $profile): ?>
                                <option value="<?= (int)$profile['id'] ?>"><?= h($profile['name']) ?></option>
                            <?php endforeach; ?>
                        </select></label>
                        <label>Telefone<input name="phone" data-phone-mask inputmode="tel" placeholder="Ex: (41) 99631-0725"></label>
                        <label>Ramal Asterisk<input name="asterisk_extension" inputmode="numeric" pattern="[0-9]*" placeholder="Ex: 1003"></label>
                        <h3 class="wide">Modulos deste usuario</h3>
                        <?= module_checkboxes(default_role_modules('usuario_operacional')) ?>
                        <button class="button">Cadastrar</button>
                    </form>
                </article>
            </section>
        <?php endif; ?>
        <?php if ($isPlatformAdmin): ?>
            <section class="panel">
                <div class="section-head">
                    <div>
                        <h2>Perfis de usuario</h2>
                        <p>Use perfis para padronizar quais modulos cada tipo de usuario pode acessar.</p>
                    </div>
                    <a class="button secondary" href="?page=users&new_profile=1">Criar perfil</a>
                </div>
                <?php if (!$accessProfiles): ?>
                    <p class="empty">Nenhum perfil criado ainda.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Perfil</th><th>Base</th><th>Usuarios</th><th>Modulos</th><th>Acoes</th></tr></thead>
                            <tbody>
                            <?php foreach ($accessProfiles as $profile): ?>
                                <?php $profileModules = modules_from_json((string)$profile['modules_json']); ?>
                                <tr>
                                    <td><?= h($profile['name']) ?></td>
                                    <td><?= h(role_label($profile['role_key'])) ?></td>
                                    <td><?= h((string)($profile['linked_users'] ?? 0)) ?></td>
                                    <td><?= h(implode(', ', array_map(fn($key) => access_modules()[$key] ?? $key, array_filter($profileModules, fn($key) => $key !== 'account')))) ?></td>
                                    <td class="actions">
                                        <a class="mini-link" href="?page=users&profile_id=<?= (int)$profile['id'] ?>">Editar</a>
                                        <?php if ((int)($profile['linked_users'] ?? 0) === 0): ?>
                                            <form method="post" class="inline" onsubmit="return confirm('Excluir este perfil de usuario?');">
                                                <input type="hidden" name="action" value="delete_access_profile">
                                                <input type="hidden" name="profile_id" value="<?= (int)$profile['id'] ?>">
                                                <button class="mini-link danger-link" type="submit">Excluir</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    <?php });
}

function render_teams(): void
{
    layout('teams', function () {
        [$userClause, $userParams] = tenant_clause();
        [$teamClause, $teamParams] = tenant_clause('t');
        $supervisors = rows("SELECT id, name FROM users WHERE {$userClause} AND role IN ('supervisor','admin_empresa') ORDER BY name", $userParams);
        ?>
        <section class="grid two">
            <form class="panel form-grid" method="post">
                <input type="hidden" name="action" value="save_team">
                <h2>Nova equipe</h2>
                <label>Nome<input name="name" required></label>
                <label>Descricao<textarea name="description"></textarea></label>
                <label>Supervisor<select name="supervisor_id"><option value="">Selecione</option><?php foreach ($supervisors as $s): ?><option value="<?= $s['id'] ?>"><?= h($s['name']) ?></option><?php endforeach; ?></select></label>
                <label>Meta diaria<input name="daily_goal" type="number" value="80"></label>
                <label>Limite simultaneo<input name="simultaneous_limit" type="number" value="1"></label>
                <label>Prioridade<input name="priority" type="number" value="1"></label>
                <label>Fila VoIP<input name="voip_queue"></label>
                <button class="button">Cadastrar</button>
            </form>
            <article class="panel">
                <h2>Equipes</h2>
                <?= table(rows("SELECT t.name, t.description, COALESCE(u.name, '-') supervisor, t.daily_goal, t.simultaneous_limit, t.voip_queue FROM teams t LEFT JOIN users u ON u.id = t.supervisor_id WHERE {$teamClause} ORDER BY t.id DESC", $teamParams), ['name', 'description', 'supervisor', 'daily_goal', 'simultaneous_limit', 'voip_queue']) ?>
            </article>
        </section>
    <?php });
}

function render_lists(): void
{
    layout('lists', function () {
        [$clause, $params] = tenant_clause();
        [$listClause, $listParams] = tenant_clause('l');
        $lists = rows("SELECT * FROM contact_lists WHERE {$clause} ORDER BY id DESC", $params);
        $listPage = max(1, (int)($_GET['lists_page'] ?? 1));
        $listsPerPage = 10;
        $listTotal = (int)scalar("SELECT COUNT(*) FROM contact_lists l WHERE {$listClause}", $listParams);
        $listPages = max(1, (int)ceil($listTotal / $listsPerPage));
        $listPage = min($listPage, $listPages);
        $listOffset = ($listPage - 1) * $listsPerPage;
        $listTableRows = rows("SELECT l.id, l.name, l.status, l.source, COUNT(c.id) contatos, SUM(CASE WHEN c.attempts > 0 OR c.last_call_at IS NOT NULL THEN 1 ELSE 0 END) chamados, l.tags, l.created_at FROM contact_lists l LEFT JOIN contacts c ON c.list_id = l.id AND c.status <> 'excluido' WHERE {$listClause} GROUP BY l.id ORDER BY l.id DESC LIMIT {$listsPerPage} OFFSET {$listOffset}", $listParams);
        $selectedListId = (int)($_GET['list_id'] ?? 0);
        $importToken = (string)($_GET['import_token'] ?? '');
        $pendingImport = $importToken ? ($_SESSION['pending_imports'][$importToken] ?? null) : null;
        if ($pendingImport && ((int)$pendingImport['company_id'] !== (int)(current_user()['company_id'] ?? 0) && !is_platform_admin())) {
            $pendingImport = null;
        }
        $leadStatusLabels = lead_reprocess_status_labels();
        $leadStatusKeys = array_values(array_diff(array_keys($leadStatusLabels), ['all']));
        $leadStatusFilters = [];
        $rawLeadStatuses = $_GET['lead_statuses'] ?? null;
        if (is_array($rawLeadStatuses)) {
            $leadStatusFilters = array_values(array_unique(array_filter(array_map(static fn($value) => strtolower(trim((string)$value)), $rawLeadStatuses), static fn($value) => in_array($value, $leadStatusKeys, true))));
        } else {
            $legacyFilter = strtolower(trim((string)($_GET['lead_status'] ?? '')));
            if ($legacyFilter !== '' && $legacyFilter !== 'all' && in_array($legacyFilter, $leadStatusKeys, true)) {
                $leadStatusFilters = [$legacyFilter];
            }
        }
        $hasLeadStatusFilter = $leadStatusFilters !== [];
        $selectedList = null;
        $contacts = [];
        $displayContacts = [];
        $contactTotal = 0;
        $hasMoreContacts = false;
        $leadStatusCounts = array_fill_keys(array_keys($leadStatusLabels), 0);
        $suggestedRemessaName = '';
        if ($selectedListId) {
            $selectedList = one("SELECT * FROM contact_lists WHERE id = ? AND {$clause}", array_merge([$selectedListId], $params));
            if ($selectedList) {
                $leadStatusCounts = list_reprocess_status_counts($selectedListId, (int)$selectedList['company_id']);
                $contactTotal = (int)$leadStatusCounts['all'];
                $batch = list_contacts_batch($selectedListId, (int)$selectedList['company_id'], 0, 10, $leadStatusFilters);
                $contacts = $batch['contacts'];
                $displayContacts = $contacts;
                $hasMoreContacts = (bool)$batch['has_more'];
                $sourceName = lead_reprocess_source_name($selectedList, (int)$selectedList['company_id']);
                $suggestedRemessaName = suggest_remessa_name((int)$selectedList['company_id'], $sourceName);
            }
        }
        $showEmail = false;
        $showCity = false;
        $showState = false;
        $showProduct = false;
        $showOrigin = false;
        $showNotes = false;
        foreach ($displayContacts as $contact) {
            $showEmail = $showEmail || trim((string)($contact['email'] ?? '')) !== '';
            $showCity = $showCity || trim((string)($contact['city'] ?? '')) !== '';
            $showState = $showState || trim((string)($contact['state'] ?? '')) !== '';
            $showProduct = $showProduct || trim((string)($contact['product'] ?? '')) !== '';
            $showOrigin = $showOrigin || trim((string)($contact['origin'] ?? '')) !== '';
            $showNotes = $showNotes || trim((string)($contact['notes'] ?? '')) !== '';
        }
        ?>
        <section class="panel">
            <form class="form-grid" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_list_import_csv">
                <h2>Nova lista com CSV</h2>
                <label>Nome da lista<input name="name" required placeholder="Ex: Leads Ademicon - Julho"></label>
                <label>Arquivo CSV<input type="file" name="csv" accept=".csv,text/csv" required></label>
                <label>Descricao<textarea name="description" placeholder="Opcional"></textarea></label>
                <label>Origem<input name="source" value="CSV"></label>
                <label>Etiquetas<input name="tags" placeholder="vendas, julho"></label>
                <p class="hint wide">Cada importacao cria uma nova lista. Para preservar o historico e os indicadores de numeros ja ligados, nao atualizamos listas que ja existem.</p>
                <div class="button-row wide">
                    <button class="button">Criar lista e vincular campos</button>
                    <a class="button secondary" href="?page=lists&download_template=1">Baixar modelo CSV</a>
                </div>
            </form>
        </section>
        <?php if ($pendingImport): ?>
            <?php $options = csv_field_options(); ?>
            <?php
            $hasScientificPhoneSample = false;
            foreach ($pendingImport['sample'] as $sampleRow) {
                foreach ($sampleRow as $sampleValue) {
                    if (phone_import_error_reason((string)$sampleValue) !== 'Telefone invalido') {
                        $hasScientificPhoneSample = true;
                        break 2;
                    }
                }
            }
            ?>
            <section class="panel">
                <h2>Vincular campos do CSV</h2>
                <p class="hint">Nova lista: <?= h((string)($pendingImport['list_data']['name'] ?? 'Lista importada')) ?>. Arquivo: <?= h($pendingImport['filename']) ?>. Selecione o campo do sistema para cada coluna importada. O campo Telefone e obrigatorio.</p>
                <?php if ($hasScientificPhoneSample): ?>
                    <div class="flash error">O CSV contem telefone em formato cientifico, como 5,542E+12. Exporte novamente com a coluna Telefone formatada como texto para preservar todos os digitos.</div>
                <?php endif; ?>
                <form method="post" class="stack">
                    <input type="hidden" name="action" value="confirm_csv_import">
                    <input type="hidden" name="list_id" value="<?= (int)$pendingImport['list_id'] ?>">
                    <input type="hidden" name="import_token" value="<?= h($importToken) ?>">
                    <div class="table-wrap"><table>
                        <thead><tr><th>Coluna do arquivo</th><th>Vincular com</th><th>Exemplos encontrados</th></tr></thead>
                        <tbody>
                        <?php foreach ($pendingImport['headers'] as $index => $header): ?>
                            <?php $suggested = guess_csv_field((string)$header); ?>
                            <tr>
                                <td><strong><?= h((string)$header) ?></strong></td>
                                <td>
                                    <select name="field_map[<?= (int)$index ?>]">
                                        <?php foreach ($options as $value => $label): ?>
                                            <option value="<?= h($value) ?>" <?= $suggested === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <?php
                                    $examples = [];
                                    foreach ($pendingImport['sample'] as $sampleRow) {
                                        $example = trim((string)($sampleRow[$index] ?? ''));
                                        if ($example !== '') {
                                            $examples[] = $example;
                                        }
                                    }
                                    echo h(implode(' | ', array_slice($examples, 0, 3)) ?: '-');
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <div class="button-row">
                        <button class="button">Confirmar importacao</button>
                        <a class="button secondary" href="?page=lists">Cancelar</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>
        <section class="panel">
            <h2>Listas</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nome</th><th>Status</th><th>Origem</th><th>Contatos</th><th>Etiquetas</th><th>Criada em</th><th>Acoes</th></tr></thead>
                    <tbody>
                    <?php foreach ($listTableRows as $list): ?>
                        <tr>
                            <td><?= h($list['name']) ?></td>
                            <td><?= h($list['status']) ?></td>
                            <td><?= h($list['source']) ?></td>
                            <td><?= h((string)$list['contatos']) ?></td>
                            <td><?= h($list['tags']) ?></td>
                            <td><?= h(datetime_utc_display((string)$list['created_at'])) ?></td>
                            <td class="actions">
                                <a class="mini-link" href="?page=lists&list_id=<?= (int)$list['id'] ?>">Ver numeros</a>
                                <?php if (is_account_admin()): ?>
                                    <form method="post" class="inline" data-reset-list-form>
                                        <input type="hidden" name="action" value="reset_list">
                                        <input type="hidden" name="list_id" value="<?= (int)$list['id'] ?>">
                                        <input type="hidden" name="reset_confirmation" value="">
                                        <button class="mini-link danger-link" type="submit">Resetar</button>
                                    </form>
                                    <?php if ((int)($list['chamados'] ?? 0) === 0): ?>
                                        <form method="post" class="inline" onsubmit="return confirm('Excluir esta lista? Esta acao nao podera ser desfeita.');">
                                            <input type="hidden" name="action" value="delete_list">
                                            <input type="hidden" name="list_id" value="<?= (int)$list['id'] ?>">
                                            <button class="mini-link danger-link" type="submit">Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($listPages > 1): ?>
                <div class="pagination table-pagination" aria-label="Paginacao das listas">
                    <?php if ($listPage > 1): ?>
                        <a class="button secondary" href="?page=lists&amp;lists_page=<?= $listPage - 1 ?>">Anterior</a>
                    <?php endif; ?>
                    <span>Pagina <?= $listPage ?> de <?= $listPages ?></span>
                    <?php if ($listPage < $listPages): ?>
                        <a class="button secondary" href="?page=lists&amp;lists_page=<?= $listPage + 1 ?>">Proxima</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php if ($selectedList): ?>
        <section class="call-modal-backdrop" data-list-numbers-modal>
            <article class="call-modal list-numbers-modal">
                <header>
                    <div>
                        <span class="modal-kicker">Contatos importados</span>
                        <h2>Numeros da lista</h2>
                        <p><?= h($selectedList['name']) ?></p>
                    </div>
                    <div class="call-modal-actions">
                        <?php if ($lists): ?>
                            <form method="get" class="inline">
                                <input type="hidden" name="page" value="lists">
                                <select name="list_id" onchange="this.form.submit()">
                                    <?php foreach ($lists as $list): ?>
                                        <option value="<?= (int)$list['id'] ?>" <?= $selectedListId === (int)$list['id'] ? 'selected' : '' ?>><?= h($list['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php endif; ?>
                        <a class="icon-button" href="?page=lists" aria-label="Fechar modal">x</a>
                    </div>
                </header>

                <?php if ($contactTotal === 0): ?>
                    <p class="empty">Nenhum numero nesta lista.</p>
                <?php elseif (!$displayContacts && !$hasMoreContacts): ?>
                    <p class="empty">Nenhuma lead corresponde ao filtro selecionado.</p>
                <?php else: ?>
                    <div class="list-remessa-toolbar">
                        <form method="get" class="list-status-filters list-status-filter-form" data-lead-status-filter-form>
                            <input type="hidden" name="page" value="lists">
                            <input type="hidden" name="list_id" value="<?= (int)$selectedListId ?>">
                            <label class="status-chip status-chip-all">
                                <input type="checkbox" data-status-filter-all <?= !$hasLeadStatusFilter || count($leadStatusFilters) === count($leadStatusKeys) ? 'checked' : '' ?>>
                                <span><?= h($leadStatusLabels['all']) ?></span>
                                <strong><?= (int)($leadStatusCounts['all'] ?? 0) ?></strong>
                            </label>
                            <?php foreach ($leadStatusKeys as $filterKey): ?>
                                <label class="status-chip">
                                    <input type="checkbox" name="lead_statuses[]" value="<?= h($filterKey) ?>" <?= in_array($filterKey, $leadStatusFilters, true) ? 'checked' : '' ?> data-status-filter-option>
                                    <span><?= h($leadStatusLabels[$filterKey]) ?></span>
                                    <strong><?= (int)($leadStatusCounts[$filterKey] ?? 0) ?></strong>
                                </label>
                            <?php endforeach; ?>
                            <button class="button small filter-accent" type="submit">Aplicar filtros</button>
                        </form>
                        <form method="post" class="remessa-create-form" id="remessa-create-form" data-create-remessa-form>
                            <input type="hidden" name="action" value="create_remessa_from_selection">
                            <input type="hidden" name="source_list_id" value="<?= (int)$selectedListId ?>">
                            <?php foreach ($leadStatusFilters as $statusKey): ?>
                                <input type="hidden" name="lead_status_filters[]" value="<?= h($statusKey) ?>">
                            <?php endforeach; ?>
                            <label>Nova remessa<input name="remessa_name" value="<?= h($suggestedRemessaName) ?>" placeholder="Nova remessa"></label>
                            <button class="button" type="submit">Criar nova remessa</button>
                            <label class="check remessa-select-all"><input type="checkbox" data-remessa-toggle-all> Selecionar exibidas</label>
                        </form>
                    </div>
                    <div class="table-wrap" data-list-infinite-scroll data-list-id="<?= (int)$selectedListId ?>" data-next-offset="10" data-has-more="<?= $hasMoreContacts ? '1' : '0' ?>" data-company-id="<?= (int)$selectedList['company_id'] ?>" data-show-email="<?= $showEmail ? '1' : '0' ?>" data-show-city="<?= $showCity ? '1' : '0' ?>" data-show-state="<?= $showState ? '1' : '0' ?>" data-show-product="<?= $showProduct ? '1' : '0' ?>" data-show-origin="<?= $showOrigin ? '1' : '0' ?>" data-show-notes="<?= $showNotes ? '1' : '0' ?>">
                        <table class="editable-table">
                            <thead>
                                <tr>
                                    <th class="select-col"><input type="checkbox" data-remessa-toggle-all aria-label="Selecionar todos os exibidos"></th>
                                    <th>Nome</th>
                                    <th>Telefone</th>
                                    <?php if ($showEmail): ?><th>Email</th><?php endif; ?>
                                    <?php if ($showCity): ?><th>Cidade</th><?php endif; ?>
                                    <?php if ($showState): ?><th>UF</th><?php endif; ?>
                                    <?php if ($showProduct): ?><th>Produto</th><?php endif; ?>
                                    <?php if ($showOrigin): ?><th>Origem</th><?php endif; ?>
                                    <?php if ($showNotes): ?><th>Observacoes</th><?php endif; ?>
                                    <th>Ligado</th>
                                    <th>Status</th>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody data-list-contact-rows>
                            <?php foreach ($displayContacts as $contact): ?>
                                <?php $formId = 'contact-form-' . (int)$contact['id']; ?>
                                <tr>
                                    <td class="select-col">
                                        <input type="checkbox" form="remessa-create-form" name="selected_contacts[]" value="<?= (int)$contact['id'] ?>" data-remessa-selection>
                                    </td>
                                    <td>
                                        <input form="<?= $formId ?>" type="hidden" name="action" value="update_contact">
                                        <input form="<?= $formId ?>" type="hidden" name="contact_id" value="<?= (int)$contact['id'] ?>">
                                        <input form="<?= $formId ?>" type="hidden" name="list_id" value="<?= (int)$contact['list_id'] ?>">
                                        <input form="<?= $formId ?>" type="hidden" name="company_id" value="<?= (int)$selectedList['company_id'] ?>">
                                        <input form="<?= $formId ?>" name="name" value="<?= h($contact['name']) ?>">
                                    </td>
                                    <td>
                                        <input form="<?= $formId ?>" name="phone" value="<?= h($contact['phone_raw'] ?: $contact['phone_e164']) ?>">
                                        <small><?= h($contact['phone_e164']) ?></small>
                                    </td>
                                        <?php if ($showEmail): ?><td><input form="<?= $formId ?>" name="email" type="email" value="<?= h($contact['email']) ?>"></td><?php endif; ?>
                                        <?php if ($showCity): ?><td><input form="<?= $formId ?>" name="city" value="<?= h($contact['city']) ?>"></td><?php endif; ?>
                                        <?php if ($showState): ?><td><input form="<?= $formId ?>" name="state" value="<?= h($contact['state']) ?>"></td><?php endif; ?>
                                        <?php if ($showProduct): ?><td><input form="<?= $formId ?>" name="product" value="<?= h($contact['product']) ?>"></td><?php endif; ?>
                                        <?php if ($showOrigin): ?><td><input form="<?= $formId ?>" name="origin" value="<?= h($contact['origin']) ?>"></td><?php endif; ?>
                                        <?php if ($showNotes): ?><td><input form="<?= $formId ?>" name="notes" value="<?= h($contact['notes']) ?>"></td><?php endif; ?>
                                        <?php $wasCalled = ((int)$contact['attempts'] > 0) || !empty($contact['last_call_at']); ?>
                                        <td><span class="status-badge <?= $wasCalled ? 'called' : '' ?>"><?= $wasCalled ? 'Sim' : 'Nao' ?></span></td>
                                        <td><?= h($contact['status']) ?></td>
                                        <td class="actions">
                                            <form id="<?= $formId ?>" method="post"></form>
                                            <button form="<?= $formId ?>" class="button small" type="submit">Salvar</button>
                                            <form method="post" onsubmit="return confirm('Remover este numero da lista?');">
                                                <input type="hidden" name="action" value="delete_contact">
                                                <input type="hidden" name="contact_id" value="<?= (int)$contact['id'] ?>">
                                                <input type="hidden" name="list_id" value="<?= (int)$contact['list_id'] ?>">
                                                <input type="hidden" name="company_id" value="<?= (int)$selectedList['company_id'] ?>">
                                                <button class="button secondary small" type="submit">Excluir</button>
                                            </form>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="list-loading-more <?= $hasMoreContacts ? '' : 'is-hidden' ?>" data-list-loading-more>Carregando mais...</div>
                    </div>
                    <p class="hint">Os contatos sao carregados em grupos de 10 durante a rolagem. A exclusao remove o numero da fila de ligacoes sem apagar historico de chamadas. As leads selecionadas viram uma nova remessa sem perder o rastreio das tentativas anteriores.</p>
                <?php endif; ?>
            </article>
        </section>
        <?php endif; ?>
        <details class="panel import-history-disclosure">
            <summary>
                <span>Ultimas importacoes</span>
                <span class="import-history-chevron" aria-hidden="true"></span>
            </summary>
            <div class="import-history-content">
                <?= table(rows("SELECT filename, total_rows, imported, duplicated, invalid, blocked, created_at FROM import_batches WHERE {$clause} ORDER BY id DESC LIMIT 10", $params), ['filename', 'total_rows', 'imported', 'duplicated', 'invalid', 'blocked', 'created_at']) ?>
            </div>
        </details>
    <?php });
}

function render_campaigns(): void
{
    layout('campaigns', function () {
        [$clause, $params] = tenant_clause();
        [$campaignClause, $campaignParams] = tenant_clause('ca');
        $editCampaignId = (int)($_GET['edit_campaign'] ?? 0);
        $editCampaign = null;
        if ($editCampaignId) {
            [$editClause, $editParams] = tenant_clause();
            $editCampaign = one("SELECT * FROM campaigns WHERE id = ? AND {$editClause} AND status <> 'Manual'", array_merge([$editCampaignId], $editParams));
            if (!$editCampaign) {
                flash('Campanha nao encontrada para edicao.', 'error');
            }
        }
        $lists = rows("SELECT l.id,l.name,(SELECT COUNT(*) FROM contacts c WHERE c.list_id=l.id AND c.status <> 'excluido') contact_count FROM contact_lists l WHERE {$clause} ORDER BY l.name", $params);
        $teams = rows("SELECT id, name FROM teams WHERE {$clause} ORDER BY name", $params);
        $supervisors = rows("SELECT id, name FROM users WHERE {$clause} AND role IN ('supervisor','admin_empresa') ORDER BY name", $params);
        $campaignPage = max(1, (int)($_GET['campaigns_page'] ?? 1));
        $campaignsPerPage = 10;
        $campaignTotal = (int)scalar("SELECT COUNT(*) FROM campaigns ca WHERE {$campaignClause} AND ca.status <> 'Manual'", $campaignParams);
        $campaignPages = max(1, (int)ceil($campaignTotal / $campaignsPerPage));
        $campaignPage = min($campaignPage, $campaignPages);
        $campaignOffset = ($campaignPage - 1) * $campaignsPerPage;
        $campaignRows = rows("SELECT ca.id, ca.name, ca.dialer_type, ca.status, COALESCE(l.name, '-') lista, COALESCE(t.name, '-') equipe, ca.max_attempts, ca.simultaneous_calls, COUNT(co.id) chamadas
            FROM campaigns ca
            LEFT JOIN contact_lists l ON l.id = ca.list_id
            LEFT JOIN teams t ON t.id = ca.team_id
            LEFT JOIN calls co ON co.campaign_id = ca.id
            WHERE {$campaignClause} AND ca.status <> 'Manual'
            GROUP BY ca.id
            ORDER BY ca.id DESC
            LIMIT {$campaignsPerPage} OFFSET {$campaignOffset}", $campaignParams);
        $editing = (bool)$editCampaign;
        $showCreateModal = isset($_GET['new_campaign']);
        $field = fn(string $key, mixed $default = '') => $editCampaign[$key] ?? $default;
        ?>
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Campanhas</h2>
                    <p>Gerencie as campanhas de discagem e seus roteiros.</p>
                </div>
                <div class="button-row">
                    <a class="button secondary" href="?page=reports#campaign-call-logs">Logs de chamadas</a>
                    <a class="button" href="?page=campaigns&new_campaign=1">Criar nova campanha</a>
                </div>
            </div>
                <?php if (!$campaignRows): ?>
                    <p class="empty">Nenhuma campanha encontrada.</p>
                <?php else: ?>
                    <div class="table-wrap"><table>
                        <thead><tr><th>Nome</th><th>Tipo</th><th>Status</th><th>Lista</th><th>Equipe</th><th>Tentativas</th><th>Simultaneas</th><th>Acoes</th></tr></thead>
                        <tbody><?php foreach ($campaignRows as $row): ?><tr>
                            <td><?= h($row['name']) ?></td>
                            <td><?= h($row['dialer_type']) ?></td>
                            <td><?= h($row['status']) ?></td>
                            <td><?= h($row['lista']) ?></td>
                            <td><?= h($row['equipe']) ?></td>
                            <td><?= h((string)$row['max_attempts']) ?></td>
                            <td><?= h((string)($row['simultaneous_calls'] ?? 1)) ?></td>
                            <td class="actions">
                                <a class="button secondary" href="?page=campaigns&amp;campaigns_page=<?= $campaignPage ?>&amp;edit_campaign=<?= (int)$row['id'] ?>">Editar</a>
                                <?php if ((int)$row['chamadas'] === 0): ?>
                                    <form method="post" onsubmit="return confirm('Excluir esta campanha? Esta acao nao podera ser desfeita.');">
                                        <input type="hidden" name="action" value="delete_campaign">
                                        <input type="hidden" name="campaign_id" value="<?= (int)$row['id'] ?>">
                                        <button class="button danger" type="submit">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr><?php endforeach; ?></tbody>
                    </table></div>
                    <?php if ($campaignPages > 1): ?>
                        <div class="pagination table-pagination" aria-label="Paginacao das campanhas">
                            <?php if ($campaignPage > 1): ?>
                                <a class="button secondary" href="?page=campaigns&amp;campaigns_page=<?= $campaignPage - 1 ?>">Anterior</a>
                            <?php endif; ?>
                            <span>Pagina <?= $campaignPage ?> de <?= $campaignPages ?></span>
                            <?php if ($campaignPage < $campaignPages): ?>
                                <a class="button secondary" href="?page=campaigns&amp;campaigns_page=<?= $campaignPage + 1 ?>">Proxima</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
        </section>
        <?php if ($showCreateModal || $editing): ?>
            <section class="call-modal-backdrop">
                <article class="call-modal campaign-modal">
                    <header>
                        <div>
                            <span class="modal-kicker"><?= $editing ? 'Editar campanha' : 'Nova campanha' ?></span>
                            <h2><?= $editing ? h((string)$field('name', 'Editar campanha')) : 'Nova campanha' ?></h2>
                            <p><?= $editing ? 'Atualize a campanha, roteiro e parametros de discagem.' : 'Crie uma campanha vinculada a uma lista de contatos.' ?></p>
                        </div>
                        <a class="icon-button" href="?page=campaigns&amp;campaigns_page=<?= $campaignPage ?>" aria-label="Fechar modal">x</a>
                    </header>
                    <form class="form-grid" method="post">
                        <input type="hidden" name="action" value="<?= $editing ? 'update_campaign' : 'create_campaign' ?>">
                        <?php if ($editing): ?><input type="hidden" name="campaign_id" value="<?= (int)$editCampaign['id'] ?>"><?php endif; ?>
                        <label>Nome<input name="name" value="<?= h((string)$field('name')) ?>" required></label>
                        <label>Descricao<textarea name="description"><?= h((string)$field('description')) ?></textarea></label>
                        <label>Lista<select name="list_id" required><?php foreach ($lists as $l): ?><option value="<?= $l['id'] ?>" <?= (int)$field('list_id') === (int)$l['id'] ? 'selected' : '' ?>><?= h($l['name'] . ' (' . (int)$l['contact_count'] . ')') ?></option><?php endforeach; ?></select></label>
                        <label>Equipe<select name="team_id"><option value="">Opcional</option><?php foreach ($teams as $t): ?><option value="<?= $t['id'] ?>" <?= (int)$field('team_id') === (int)$t['id'] ? 'selected' : '' ?>><?= h($t['name']) ?></option><?php endforeach; ?></select></label>
                        <label>Supervisor<select name="supervisor_id"><option value="">Opcional</option><?php foreach ($supervisors as $s): ?><option value="<?= $s['id'] ?>" <?= (int)$field('supervisor_id') === (int)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option><?php endforeach; ?></select></label>
                        <label>Tipo de discador<select name="dialer_type"><?php foreach (['progressivo' => 'Progressivo', 'preview' => 'Preview', 'manual' => 'Manual'] as $value => $label): ?><option value="<?= h($value) ?>" <?= (string)$field('dialer_type', 'progressivo') === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label>
                        <label>Numero exibido<input name="caller_id" value="<?= h((string)$field('caller_id', '+554130000000')) ?>"></label>
                        <label>Tronco SIP<input name="sip_trunk" value="<?= h((string)$field('sip_trunk', 'Tronco simulado')) ?>"></label>
                        <label>Inicio<input name="starts_at" type="datetime-local" value="<?= h(datetime_local((string)$field('starts_at'))) ?>"></label>
                        <label>Fim<input name="ends_at" type="datetime-local" value="<?= h(datetime_local((string)$field('ends_at'))) ?>"></label>
                        <label>Horario<input name="call_window" value="<?= h((string)$field('call_window', '08:00-18:00')) ?>"></label>
                        <label>Max. tentativas<input name="max_attempts" type="number" min="1" value="<?= h((string)$field('max_attempts', '1')) ?>"></label>
                        <label>Chamadas simultaneas (Asterisk)<input name="simultaneous_calls" type="number" min="1" max="10" step="1" value="<?= h((string)$field('simultaneous_calls', '1')) ?>"></label>
                        <label>Intervalo min.<input name="retry_interval_minutes" type="number" value="<?= h((string)$field('retry_interval_minutes', '240')) ?>"></label>
                        <label>Prioridade<input name="priority" type="number" value="<?= h((string)$field('priority', '1')) ?>"></label>
                        <label>Status<select name="status"><?php foreach (['Ativa', 'Rascunho', 'Agendada', 'Pausada', 'Finalizada'] as $status): ?><option <?= (string)$field('status', 'Ativa') === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select></label>
                        <label class="check"><input type="checkbox" name="recording_enabled" <?= (int)$field('recording_enabled') ? 'checked' : '' ?>> Gravacao habilitada</label>
                        <label class="wide">Roteiro<textarea name="script" rows="5"><?= h((string)$field('script', 'Confirme os dados do lead, entenda o objetivo da carta, apresente a oferta e registre o resultado.')) ?></textarea></label>
                        <button class="button"><?= $editing ? 'Salvar alteracoes' : 'Criar campanha' ?></button>
                    </form>
                </article>
            </section>
        <?php endif; ?>
    <?php });
}

function render_radar(): void
{
    layout('radar', function () {
        $user = current_user();
        $stored = $_SESSION['radar_leads'] ?? [];
        $sameTenant = (int)($stored['company_id'] ?? 0) === (int)$user['company_id'];
        $filters = $sameTenant && is_array($stored['filters'] ?? null) ? $stored['filters'] : [];
        $places = $sameTenant && is_array($stored['places'] ?? null) ? $stored['places'] : [];
        $targetCount = max(1, (int)($stored['target_count'] ?? 20));
        $activeListId = $sameTenant ? (int)($stored['list_id'] ?? 0) : 0;
        $listRows = rows("SELECT l.id,l.name,l.radar_target_leads,COUNT(c.id) contact_count FROM contact_lists l LEFT JOIN contacts c ON c.list_id=l.id AND c.status <> 'excluido' WHERE l.company_id=? GROUP BY l.id ORDER BY l.id DESC", [(int)$user['company_id']]);
        $radarLists = rows("SELECT l.id,l.name,l.status,l.source,l.tags,l.created_at,activity.latest_history_id,COUNT(c.id) contatos
            FROM contact_lists l
            INNER JOIN (
                SELECT list_id,MAX(id) latest_history_id
                FROM radar_lead_history
                WHERE company_id=? AND list_id IS NOT NULL
                GROUP BY list_id
            ) activity ON activity.list_id=l.id
            LEFT JOIN contacts c ON c.list_id=l.id AND c.status <> 'excluido'
            WHERE l.company_id=?
            GROUP BY l.id
            ORDER BY activity.latest_history_id DESC,l.id DESC", [(int)$user['company_id'], (int)$user['company_id']]);
        $latestRadarListId = (int)($radarLists[0]['id'] ?? 0);
        $historyListIds = [];
        if ($places) {
            $marks = implode(',', array_fill(0, count($places), '?'));
            foreach (rows("SELECT place_id,list_id FROM radar_lead_history WHERE company_id=? AND place_id IN ($marks)", array_merge([(int)$user['company_id']], array_column($places, 'place_id'))) as $history) {
                $historyListIds[(string)$history['place_id']] = (int)($history['list_id'] ?? 0);
            }
        }
        $activeList = null;
        foreach ($listRows as $row) if ((int)$row['id'] === $activeListId) { $activeList = $row; break; }
        $activeTotal = (int)($activeList['contact_count'] ?? 0);
        ?>
        <section class="panel">
            <div class="section-head">
                <div><h2>Radar de Leads</h2><p>Encontre empresas novas no Google Places, sem repetir empresas ja salvas em listas ou importadas.</p></div>
            </div>
            <form method="post" class="form-grid" data-radar-loading-form>
                <input type="hidden" name="action" value="search_radar_leads">
                <label>Segmento<input name="segment" required value="<?= h((string)($filters['segment'] ?? '')) ?>" placeholder="Ex: imobiliaria, construtora"></label>
                <label>Estado<input name="state" required maxlength="2" value="<?= h((string)($filters['state'] ?? '')) ?>" placeholder="PR"></label>
                <label>Cidade<input name="city" required value="<?= h((string)($filters['city'] ?? '')) ?>" placeholder="Curitiba"></label>
                <label>Bairro<input name="neighborhood" value="<?= h((string)($filters['neighborhood'] ?? '')) ?>" placeholder="Opcional"></label>
                <label>Rua<input name="street" value="<?= h((string)($filters['street'] ?? '')) ?>" placeholder="Opcional"></label>
                <label>Quantidade desejada<input name="target_count" type="number" min="1" max="1000" value="<?= h((string)$targetCount) ?>"></label>
                <label class="check"><input type="checkbox" name="only_with_phone" <?= !empty($filters['only_with_phone']) ? 'checked' : '' ?>> Somente com telefone</label>
                <button class="button" type="submit">Buscar empresas</button>
            </form>
        </section>
        <?php if ($places): ?>
        <section class="panel">
            <div class="section-head"><div><h2>Empresas encontradas</h2><p>Meta: <?= h((string)$targetCount) ?> | Lista atual: <?= h((string)$activeTotal) ?> | Novas nesta busca: <?= h((string)count($places)) ?> | Descartadas: <?= h((string)($stored['discarded'] ?? 0)) ?></p></div></div>
            <form method="post">
                <div class="form-grid compact-form">
                    <label>Nome da nova lista<input name="list_name" value="<?= h('Radar ' . ($filters['segment'] ?? '') . ' - ' . ($filters['city'] ?? '')) ?>"></label>
                    <label>Meta da lista<input name="target_count" type="number" min="1" max="1000" value="<?= h((string)$targetCount) ?>"></label>
                    <label>Adicionar em lista existente<select name="list_id"><option value="">Selecione</option><?php foreach ($listRows as $list): ?><option value="<?= (int)$list['id'] ?>" <?= (int)$list['id'] === $activeListId ? 'selected' : '' ?>><?= h($list['name']) ?> (<?= (int)$list['contact_count'] ?>)</option><?php endforeach; ?></select></label>
                    <button class="button" type="submit" name="action" value="create_radar_list">Criar lista</button>
                    <button class="button secondary" type="submit" name="action" value="add_radar_to_list">Adicionar selecionadas</button>
                </div>
            <div class="table-wrap"><table>
                <thead><tr><th><input type="checkbox" data-radar-select-all></th><th>Nome</th><th>Telefone</th><th>Endereco</th><th>Site</th><th>Nota</th><th>Google Maps</th><th>Status</th></tr></thead>
                <tbody><?php foreach ($places as $place): ?>
                    <?php $linkedListId = (int)($historyListIds[$place['place_id']] ?? 0); $canSelect = $place['phone'] !== '' && $linkedListId === 0; ?>
                    <tr>
                        <td><?= $canSelect ? '<input type="checkbox" name="place_ids[]" value="' . h($place['place_id']) . '">' : '' ?></td>
                        <td><?= h($place['name']) ?></td>
                        <td><?= h($place['phone'] ?: '-') ?></td>
                        <td><?= h($place['address'] ?: '-') ?></td>
                        <td><?= $place['website'] ? '<a class="mini-link" href="' . h($place['website']) . '" target="_blank" rel="noopener">Abrir site</a>' : '-' ?></td>
                        <td><?= h($place['rating']) ?></td>
                        <td><?= $place['maps_url'] ? '<a class="mini-link" href="' . h($place['maps_url']) . '" target="_blank" rel="noopener">Ver no Maps</a>' : '-' ?></td>
                        <td><span class="status-badge <?= $linkedListId ? 'called' : '' ?>"><?= $linkedListId ? 'Adicionada a lista' : ($place['phone'] === '' ? 'Sem telefone' : 'Nova') ?></span></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
            </form>
            <div class="actions-row">
                <?php if (!empty($stored['next_page_token'])): ?><form method="post" data-radar-loading-form><input type="hidden" name="action" value="search_radar_more"><button class="button secondary" type="submit">Buscar mais empresas</button></form><?php else: ?><span class="muted">Nao ha mais empresas disponiveis para estes filtros.</span><?php endif; ?>
                <?php if ($activeListId): ?><form method="post" class="inline-form"><input type="hidden" name="action" value="create_radar_campaign"><input type="hidden" name="list_id" value="<?= $activeListId ?>"><input name="campaign_name" placeholder="Nome da campanha (opcional)"><button class="button" type="submit">Criar campanha</button></form><?php endif; ?>
            </div>
        </section>
        <?php elseif ($sameTenant && $filters): ?>
        <section class="panel"><p class="empty">Nenhuma empresa encontrada para os filtros informados.</p></section>
        <?php endif; ?>
        <?php if ($radarLists): ?>
        <section class="panel">
            <h2>Histórico de Listas</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nome</th><th>Status</th><th>Origem</th><th>Contatos</th><th>Etiquetas</th><th>Criada em</th><th>Acoes</th></tr></thead>
                    <tbody><?php foreach ($radarLists as $list): ?>
                        <?php $isLatestRadarList = (int)$list['id'] === $latestRadarListId; ?>
                        <tr class="<?= $isLatestRadarList ? 'radar-list-latest' : '' ?>">
                            <td><?= h($list['name']) ?><?= $isLatestRadarList ? '<span class="radar-latest-label">Mais recente</span>' : '' ?></td>
                            <td><?= h($list['status']) ?></td>
                            <td><?= h($list['source']) ?></td>
                            <td><?= h((string)$list['contatos']) ?></td>
                            <td><?= h($list['tags']) ?></td>
                            <td><?= h(datetime_utc_display((string)$list['created_at'])) ?></td>
                            <td><a class="mini-link" href="?page=lists&list_id=<?= (int)$list['id'] ?>">Ver numeros</a></td>
                        </tr>
                    <?php endforeach; ?></tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
        <div class="radar-loading-overlay is-hidden" data-radar-loading-overlay role="status" aria-live="polite" aria-label="Buscando empresas">
            <div class="radar-loading-indicator">
                <span class="radar-loading-spinner" aria-hidden="true"></span>
                <strong>Buscando empresas...</strong>
            </div>
        </div>
    <?php });
}

function answered_calls_batch(int $companyId, int $userId, int $offset, int $limit = 15): array
{
    $offset = max(0, $offset);
    $limit = max(1, min(50, $limit));
    $calls = rows("
        SELECT co.id, co.campaign_id, co.contact_id, co.created_at, co.started_at, co.answered_at, co.ended_at, co.destination_number, co.origin_number, co.status, co.duration_seconds, co.result_id, co.notes,
               ct.name contato, ct.email, ct.city, ct.state, ct.product, ct.origin contato_origem, ct.attempts, ct.notes contato_observacoes, ct.custom_json,
               ca.name campanha, COALESCE(cr.name, '-') resultado,
               cb.scheduled_at callback_at, cb.priority callback_priority, cb.status callback_status, cb.reason callback_reason,
               EXISTS(
                   SELECT 1 FROM call_events ce
                   WHERE ce.company_id = co.company_id AND ce.call_id = co.id AND ce.event_name = 'sip.answered'
               ) AS ever_answered
        FROM calls co
        LEFT JOIN contacts ct ON ct.id = co.contact_id AND ct.company_id = co.company_id
        LEFT JOIN campaigns ca ON ca.id = co.campaign_id AND ca.company_id = co.company_id
        LEFT JOIN call_results cr ON cr.id = co.result_id
        LEFT JOIN callbacks cb ON cb.id = (
            SELECT cbx.id FROM callbacks cbx
            WHERE cbx.company_id = co.company_id AND cbx.agent_id = co.agent_id AND cbx.contact_id = co.contact_id
              AND (cbx.call_id = co.id OR cbx.call_id IS NULL)
            ORDER BY (cbx.call_id IS NULL) ASC, cbx.id DESC LIMIT 1
        )
        WHERE co.company_id = ? AND co.agent_id = ?
          AND (co.answered_at IS NOT NULL OR co.status IN ('answered','connected','completed','em_atendimento','atendida','atendido','pos_atendimento') OR co.duration_seconds > 0)
        ORDER BY co.id DESC
        LIMIT " . ($limit + 1) . " OFFSET " . $offset, [$companyId, $userId]);
    $hasMore = count($calls) > $limit;
    return ['calls' => array_slice($calls, 0, $limit), 'has_more' => $hasMore];
}

function answered_call_row_html(array $call): string
{
    $duration = call_conversation_duration_seconds($call);
    $durationText = $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '00:00';
    $isAnsweredCall = !empty($call['ever_answered']) && $duration > 5;
    $phoneDigits = nvoip_phone_digits((string)$call['destination_number']);
    if (strlen($phoneDigits) === 10 || strlen($phoneDigits) === 11) $phoneDigits = '55' . $phoneDigits;
    $whatsappLink = $phoneDigits !== '' ? 'https://wa.me/' . $phoneDigits : '';
    ob_start();
    ?>
    <tr class="<?= $isAnsweredCall ? 'call-history-attended' : '' ?>">
        <td><?= h(datetime_utc_display((string)($call['answered_at'] ?: $call['created_at']))) ?></td>
        <td><?= h($call['contato'] ?: 'Sem nome') ?></td>
        <td><span class="phone-inline-actions"><span><?= h($call['destination_number']) ?></span><?php if ($whatsappLink): ?><a class="mini-link whatsapp-link" href="<?= h($whatsappLink) ?>" target="_blank" rel="noopener">WhatsApp</a><?php endif; ?></span></td>
        <td><?= h($call['campanha'] ?: '-') ?></td>
        <td><?= h($durationText) ?></td>
        <td><?= h($call['resultado']) ?></td>
        <td><?php if ($isAnsweredCall): ?><span class="call-history-badge">Atendida +5s</span><?php endif; ?> <?= h($call['status']) ?></td>
        <td><button class="mini-link" type="button" data-open-call-history="<?= (int)$call['id'] ?>">Atendimento</button></td>
    </tr>
    <?php
    return (string)ob_get_clean();
}

function answered_call_modal_html(array $call, array $user, array $results): string
{
    $duration = call_conversation_duration_seconds($call);
    $durationText = $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '00:00';
    $phoneDigits = nvoip_phone_digits((string)$call['destination_number']);
    if (strlen($phoneDigits) === 10 || strlen($phoneDigits) === 11) $phoneDigits = '55' . $phoneDigits;
    $whatsappMessage = 'Oi, aqui e ' . (string)$user['name'] . ', conforme combinado, vamos seguir nossa conversa por aqui ';
    $whatsappLink = $phoneDigits !== '' ? 'https://wa.me/' . $phoneDigits . '?text=' . rawurlencode($whatsappMessage) : '';
    $callbackAtValue = !empty($call['callback_at']) ? datetime_local((string)$call['callback_at']) : '';
    $callbackDisplay = '';
    if (!empty($call['callback_at'])) {
        try {
            $callbackDisplay = datetime_utc_display((string)$call['callback_at'], 'd/m/Y H:i');
        } catch (Throwable) {
            $callbackDisplay = (string)$call['callback_at'];
        }
    }
    $callbackPriority = (string)($call['callback_priority'] ?: 'normal');
    $customFields = json_decode((string)($call['custom_json'] ?? ''), true);
    $customFields = is_array($customFields) ? $customFields : [];
    ob_start();
    ?>
    <section class="call-modal-backdrop is-hidden" data-call-history-modal="<?= (int)$call['id'] ?>">
        <article class="call-modal">
            <header><div><span class="modal-kicker">Chamada atendida</span><h2><?= h($call['contato'] ?: 'Cliente sem nome') ?></h2></div><div class="call-modal-actions"><strong><?= h($call['status']) ?></strong><button type="button" class="icon-button" data-call-history-close aria-label="Fechar modal">x</button></div></header>
            <div class="call-modal-grid">
                <dl>
                    <dt>Telefone</dt><dd><span class="modal-phone-actions"><?php if ($whatsappLink): ?><a class="whatsapp-phone-link" href="<?= h($whatsappLink) ?>" target="_blank" rel="noopener" title="Conversar pelo WhatsApp"><?= h($call['destination_number']) ?></a><?php else: ?><span><?= h($call['destination_number']) ?></span><?php endif; ?><button class="mini-link danger-link" type="button" data-quick-block-call="<?= (int)$call['id'] ?>">Bloquear</button></span></dd>
                    <dt>E-mail</dt><dd><?= h($call['email'] ?: '-') ?></dd><dt>Origem</dt><dd><?= h($call['contato_origem'] ?: '-') ?></dd><dt>Cidade</dt><dd><?= h(trim((string)$call['city'] . ' / ' . (string)$call['state'], ' /') ?: '-') ?></dd><dt>Produto</dt><dd><?= h($call['product'] ?: '-') ?></dd><dt>Campanha</dt><dd><?= h($call['campanha'] ?: '-') ?></dd>
                    <?php if (!empty($call['contato_observacoes'])): ?><dt>Observacao do contato</dt><dd><?= nl2br(h((string)$call['contato_observacoes'])) ?></dd><?php endif; ?>
                    <?php if ($callbackDisplay): ?><dt>Retorno agendado</dt><dd><?= h($callbackDisplay) ?></dd><?php endif; ?>
                    <?php if (!empty($call['callback_status'])): ?><dt>Status do retorno</dt><dd><?= h((string)$call['callback_status']) ?></dd><?php endif; ?>
                    <?php if (!empty($call['callback_reason'])): ?><dt>Motivo do retorno</dt><dd><?= h((string)$call['callback_reason']) ?></dd><?php endif; ?>
                    <?php foreach ($customFields as $key => $value): ?><?php if (is_scalar($value) && trim((string)$value) !== ''): ?><dt><?= h((string)$key) ?></dt><dd><?= h((string)$value) ?></dd><?php endif; ?><?php endforeach; ?>
                </dl>
                <div class="live-call-card is-live"><div class="live-indicator"><span></span> Atendimento registrado</div><strong><?= h($durationText) ?></strong><small><?= h($call['destination_number']) ?></small></div>
            </div>
            <form method="post" class="stack"><input type="hidden" name="action" value="update_answered_call"><input type="hidden" name="campaign_id" value="<?= (int)($call['campaign_id'] ?? 0) ?>"><input type="hidden" name="call_id" value="<?= (int)$call['id'] ?>"><label>Resultado<select name="result_id"><?php foreach ($results as $result): ?><option value="<?= (int)$result['id'] ?>" <?= (int)$call['result_id'] === (int)$result['id'] ? 'selected' : '' ?>><?= h($result['name']) ?></option><?php endforeach; ?></select></label><label>Observacoes<textarea name="notes" rows="5"><?= h((string)$call['notes']) ?></textarea></label><label>Agendar retorno<input name="callback_at" type="datetime-local" value="<?= h($callbackAtValue) ?>"></label><label>Prioridade<select name="callback_priority"><option <?= $callbackPriority === 'normal' ? 'selected' : '' ?>>normal</option><option <?= $callbackPriority === 'alta' ? 'selected' : '' ?>>alta</option><option <?= $callbackPriority === 'urgente' ? 'selected' : '' ?>>urgente</option></select></label><button class="button" type="submit">Salvar atendimento</button></form>
        </article>
    </section>
    <?php
    return (string)ob_get_clean();
}

function handle_answered_calls_batch(): void
{
    require_login();
    $user = current_user();
    $offset = max(15, min(10000, (int)($_GET['offset'] ?? 15)));
    $batch = answered_calls_batch((int)$user['company_id'], (int)$user['id'], $offset, 15);
    ensure_call_results_for_company((int)$user['company_id']);
    $results = rows('SELECT id, name, action FROM call_results WHERE company_id = ? OR company_id IS NULL ORDER BY is_default DESC, id', [(int)$user['company_id']]);
    $rowsHtml = '';
    $modalsHtml = '';
    foreach ($batch['calls'] as $call) {
        $rowsHtml .= answered_call_row_html($call);
        $modalsHtml .= answered_call_modal_html($call, $user, $results);
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'rows_html' => $rowsHtml, 'modals_html' => $modalsHtml, 'count' => count($batch['calls']), 'has_more' => $batch['has_more']], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_GET['page'] ?? '') === 'answered_calls_batch') {
    handle_answered_calls_batch();
}

function handle_agent_batch_state(): never
{
    require_login();
    $user = current_user();
    header('Content-Type: application/json; charset=utf-8');
    if (!$user || !can('agent')) {
        http_response_code(403);
        echo json_encode_safe(['ok' => false, 'error' => 'Sem permissao para consultar o lote.']);
        exit;
    }
    $state = agent_parallel_batch_state((int)$user['id'], (int)$user['company_id']);
    if (!$state) {
        echo json_encode_safe(['ok' => true, 'active' => false]);
        exit;
    }
    unset($state['batch']);
    echo json_encode_safe(['ok' => true, 'active' => true, 'batch' => $state]);
    exit;
}

if (($_GET['page'] ?? '') === 'agent_batch_state') {
    handle_agent_batch_state();
}

function render_agent(): void
{
    layout('agent', function () {
        $user = current_user();
        [$clause, $params] = tenant_clause();
        $campaigns = rows("SELECT id, name, status FROM campaigns WHERE {$clause} AND status <> 'Manual' ORDER BY status, name", $params);
        $campaignId = selected_campaign_id();
        $campaign = $campaignId ? one('SELECT * FROM campaigns WHERE id = ?', [$campaignId]) : null;
        $isAutoDialing = ($user['status'] ?? '') === 'Discando automatico';
        [$agentStatsClause, $agentStatsParams] = tenant_clause('c');
        [$todayStartUtc, $todayEndUtc] = sao_paulo_utc_period_bounds('day');
        $agentCostMicros = call_cost_sql('c');
        $agentStats = one("SELECT
                COUNT(*) chamadas_hoje,
                COALESCE(SUM(c.billable_seconds), 0) segundos_hoje,
                COALESCE(SUM({$agentCostMicros}), 0) gasto_hoje_micros
            FROM calls c
            WHERE {$agentStatsClause} AND c.created_at >= ? AND c.created_at < ?",
            array_merge($agentStatsParams, [$todayStartUtc, $todayEndUtc])) ?: [];
        $agentCards = [
            'Chamadas hoje' => (int)($agentStats['chamadas_hoje'] ?? 0),
            'Minutos hoje' => number_format(((float)($agentStats['segundos_hoje'] ?? 0)) / 60, 0, ',', '.'),
            'Gasto hoje' => money(((int)($agentStats['gasto_hoje_micros'] ?? 0)) / 1000000),
            'Leads restantes' => (int)(one("SELECT COUNT(*) v FROM contacts c WHERE {$agentStatsClause} AND c.status <> 'excluido' AND NOT EXISTS (SELECT 1 FROM calls co WHERE co.company_id = c.company_id AND co.contact_id = c.id)", $agentStatsParams)['v'] ?? 0),
        ];
        $batchState = $isAutoDialing ? agent_parallel_batch_state((int)$user['id'], (int)$user['company_id']) : null;
        $activeBatch = $batchState['batch'] ?? null;
        if ($batchState && (int)$batchState['active_count'] === 0 && empty($batchState['winner_call_id'])) {
            asterisk_continue_batch_if_exhausted((int)$batchState['batch_id']);
            $batchState = null;
            $activeBatch = null;
        }
        if ($activeBatch && !empty($activeBatch['winner_call_id'])) {
            $activeCall = one("SELECT * FROM calls WHERE id = ? AND agent_id = ? AND company_id = ? AND race_outcome = 'WINNER' LIMIT 1", [(int)$activeBatch['winner_call_id'], (int)$user['id'], (int)$user['company_id']]);
        } else {
            $activeCall = $isAutoDialing && $campaignId
                ? one("SELECT * FROM calls WHERE agent_id = ? AND company_id = ? AND campaign_id = ? AND status IN (" . active_call_statuses_sql() . ") AND COALESCE(race_outcome, '') NOT IN ('LOSER','LATE_ANSWERED') ORDER BY id DESC LIMIT 1", [(int)$user['id'], (int)$user['company_id'], $campaignId])
                : get_active_call((int)$user['id'], (int)$user['company_id']);
        }
        $isBatchWaitingForWinner = (bool)($batchState['awaiting_winner'] ?? false);
        if ($isBatchWaitingForWinner) $activeCall = null;
        $isConnectedBatchWinner = $activeBatch && !empty($activeBatch['winner_call_id']) && $activeCall && empty($activeCall['finalized_at']);
        $isCallLive = $isBatchWaitingForWinner || $isConnectedBatchWinner || ($activeCall && in_array((string)$activeCall['status'], ['in_progress', 'calling_origin', 'ringing', 'answered'], true));
        $reserved = $activeCall ? one('SELECT * FROM contacts WHERE id = ? AND company_id = ?', [$activeCall['contact_id'], $activeCall['company_id']]) : null;
        if (!$reserved && $campaign && !$isBatchWaitingForWinner) {
            $reserved = one("SELECT * FROM contacts WHERE company_id = ? AND list_id = ? AND reserved_by = ? AND status IN ('reservado','em_ligacao') ORDER BY reserved_at DESC LIMIT 1", [$campaign['company_id'], $campaign['list_id'], $user['id']]);
        }
        $autoNextPhone = (string)($_SESSION['auto_next_phone'] ?? '');
        if ($autoNextPhone !== '') {
            unset($_SESSION['auto_next_phone']);
        }
        if ($isAutoDialing && !$activeBatch && !$activeCall && !$reserved && $autoNextPhone === '') {
            db()->prepare("UPDATE users SET status='Disponivel' WHERE id=? AND company_id=? AND status='Discando automatico'")
                ->execute([(int)$user['id'], (int)$user['company_id']]);
            $user['status'] = 'Disponivel';
            $isAutoDialing = false;
            $isBatchWaitingForWinner = false;
            $isCallLive = false;
        }
        $showAnsweredModal = call_was_answered($activeCall);
        $lastCall = one("SELECT co.*, ct.name contato FROM calls co LEFT JOIN contacts ct ON ct.id = co.contact_id WHERE co.agent_id = ? ORDER BY co.id DESC LIMIT 1", [$user['id']]);
        $recentHistory = recent_phone_history((int)$user['company_id'], (int)$user['id']);
        $recentCalls = $recentHistory['todas'];
        $recentReceived = $recentHistory['recebidas'];
        $recentMade = $recentHistory['realizadas'];
        $recentMissed = $recentHistory['perdidas'];
        $answeredCallsBatch = answered_calls_batch((int)$user['company_id'], (int)$user['id'], 0, 15);
        $answeredCalls = $answeredCallsBatch['calls'];
        $answeredCallsHasMore = (bool)$answeredCallsBatch['has_more'];
        $phoneContacts = rows("SELECT name, phone_e164, product, status FROM contacts WHERE company_id = ? AND status <> 'excluido' ORDER BY last_call_at DESC, id DESC LIMIT 8", [(int)$user['company_id']]);
        ensure_call_results_for_company((int)$user['company_id']);
        $results = rows('SELECT id, name, action FROM call_results WHERE company_id = ? OR company_id IS NULL ORDER BY is_default DESC, id', [(int)$user['company_id']]);
        $renderPhoneHistory = function (array $items, string $empty) {
            if (!$items) {
                echo '<p class="phone-empty">' . h($empty) . '</p>';
                return;
            }
            foreach ($items as $index => $call) {
                $location = trim((string)($call['city'] ?: $call['state'] ?: 'Contato'), ' /');
                $duration = (int)($call['duration_seconds'] ?? 0);
                $durationText = $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '';
                $time = $call['created_at'] ? datetime_utc_display((string)$call['created_at'], 'H:i:s') : '';
                $meta = trim($location . ($time ? ' - ' . $time : '') . ($durationText ? ' - ' . $durationText : ''));
                $badgeClass = ['green', 'orange', 'blue'][$index % 3];
                ?>
                <button type="button" class="phone-history-item" data-fill-phone="<?= h($call['destination_number']) ?>" data-phone-search="<?= h(strtolower(($call['contato'] ?? '') . ' ' . ($call['destination_number'] ?? '') . ' ' . $meta)) ?>">
                    <span class="phone-history-badge <?= h($badgeClass) ?>"><?= h((string)max(1, 5 - $index)) ?></span>
                    <span class="phone-history-main">
                        <strong><?= h($call['destination_number']) ?></strong>
                        <small><?= h($meta ?: ($call['resultado'] ?: $call['status'])) ?></small>
                    </span>
                    <span class="phone-history-actions">&#9742; &#8942;</span>
                </button>
                <?php
            }
        };
        ?>
        <section class="metric-grid compact">
            <?php foreach ($agentCards as $label => $value): ?>
                <article class="metric"><span><?= h($label) ?></span><strong><?= h((string)$value) ?></strong></article>
            <?php endforeach; ?>
        </section>
        <section class="agent-layout">
            <article class="panel">
                <h2>Operação</h2>
                <form method="get" class="inline">
                    <input type="hidden" name="page" value="agent">
                    <select name="campaign_id" onchange="this.form.submit()"><?php foreach ($campaigns as $c): ?><option value="<?= $c['id'] ?>" <?= $campaignId === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['name'] . ' - ' . $c['status']) ?></option><?php endforeach; ?></select>
                </form>
                <form method="post" class="button-row">
                    <input type="hidden" name="action" value="agent_status">
                    <input type="hidden" name="campaign_id" value="<?= $campaignId ?>">
                    <button
                        name="status"
                        value="Disponivel"
                        class="button <?= $isAutoDialing ? 'danger is-locked' : 'success' ?>"
                        <?= $isAutoDialing ? 'type="button" aria-disabled="true"' : 'type="submit"' ?>
                    ><?= $isAutoDialing ? 'Atendimento em andamento' : 'Iniciar atendimento' ?></button>
                    <?php if ($isAutoDialing): ?><span class="operation-online"><span></span> Online</span><?php endif; ?>
                    <button name="status" value="Pausa" class="button secondary" data-pause-operation>Pausa</button>
                    <button type="button" class="button danger" data-floating-stop-call <?= $isCallLive ? '' : 'hidden' ?>>Parar ligacao</button>
                </form>
                <?php if ($isBatchWaitingForWinner): ?>
                    <p class="muted">Lote Asterisk em andamento: aguardando a primeira chamada atendida.</p>
                <?php endif; ?>
                <?php if ($campaign): ?>
                    <div class="script-box"><strong>Roteiro</strong><p><?= nl2br(h($campaign['script'])) ?></p></div>
                <?php endif; ?>
            </article>
            <article class="panel contact-card">
                <h2><?= $isBatchWaitingForWinner ? 'Discagem simultânea' : 'Lead atual' ?></h2>
                <?php if ($isBatchWaitingForWinner): ?>
                    <div class="parallel-dial-state" data-parallel-batch-state data-batch-id="<?= (int)$batchState['batch_id'] ?>">
                        <div class="parallel-dial-heading"><span class="parallel-dial-pulse" aria-hidden="true"></span><strong><span data-batch-active><?= (int)$batchState['active_count'] ?></span> chamadas em andamento</strong></div>
                        <p class="parallel-dial-waiting">Aguardando primeiro atendimento...</p>
                        <p class="parallel-dial-campaign">Campanha: <strong><?= h((string)($batchState['campaign_name'] ?: ($campaign['name'] ?? '-'))) ?></strong></p>
                        <div class="parallel-dial-counts" aria-label="Contadores do lote">
                            <span><strong data-batch-originated><?= (int)$batchState['originated_count'] ?></strong> originadas</span>
                            <span><strong data-batch-ringing><?= (int)$batchState['ringing_count'] ?></strong> chamando</span>
                            <span><strong data-batch-answered><?= (int)$batchState['answered_count'] ?></strong> atendidas</span>
                            <span><strong data-batch-finalized><?= (int)$batchState['finalized_count'] ?></strong> finalizadas</span>
                        </div>
                        <small>Solicitadas: <?= (int)$batchState['requested_count'] ?> · lote efetivo: <?= (int)$batchState['effective_count'] ?></small>
                    </div>
                <?php elseif ($reserved): ?>
                    <div class="contact-name"><?= h($reserved['name'] ?: 'Sem nome') ?></div>
                    <div class="contact-phone"><?= h($reserved['phone_e164']) ?></div>
                    <dl>
                        <dt>Origem</dt><dd><?= h($reserved['organization'] ?: $reserved['origin']) ?></dd>
                        <dt>Cidade</dt><dd><?= h(trim($reserved['city'] . ' / ' . $reserved['state'], ' /')) ?></dd>
                        <dt>Produto</dt><dd><?= h($reserved['product']) ?></dd>
                        <dt>Tentativas</dt><dd><?= h((string)$reserved['attempts']) ?></dd>
                    </dl>
                    <?php $custom = json_decode($reserved['custom_json'] ?: '{}', true) ?: []; if (is_array($custom) && $custom): ?>
                        <h3>Campos personalizados</h3>
                        <dl>
                            <?php foreach ($custom as $key => $value): ?>
                                <?php $displayValue = is_scalar($value) || $value === null ? (string)$value : json_encode_safe($value); ?>
                                <dt><?= h((string)$key) ?></dt><dd><?= h($displayValue) ?></dd>
                            <?php endforeach; ?>
                        </dl>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="empty">Nenhum lead reservado.</p>
                    <?php if ($isAutoDialing && $campaignId): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="reserve_contact">
                        <input type="hidden" name="campaign_id" value="<?= $campaignId ?>">
                        <button class="button">Buscar próximo lead</button>
                    </form>
                    <?php endif; ?>
                <?php endif; ?>
            </article>
            <article class="panel">
                <h2>Chamada</h2>
                <?php $callConfig = nvoip_config((int)$user['company_id']); $originatingSip = $callConfig['user_sip'] ?: ($user['extension'] ?? '') ?: ($callConfig['numbersip'] ?? ''); ?>
                <?php if ($isBatchWaitingForWinner): ?>
                    <div class="live-call-card parallel-call-card is-live">
                        <div class="live-indicator"><span></span> Discagem simultânea</div>
                        <strong>Aguardando chamada vencedora</strong>
                        <small>Somente o primeiro contato que atender será apresentado para o atendimento.</small>
                    </div>
                    <p class="empty">As chamadas concorrentes podem ser acompanhadas individualmente apenas no Diagnóstico Asterisk.</p>
                <?php else: ?>
                <div class="live-call-card <?= $isCallLive ? 'is-live' : '' ?>">
                    <div class="live-indicator"><span></span><?= $isCallLive ? 'Chamada SIP em andamento' : 'Aguardando chamada' ?></div>
                    <strong><?= h($activeCall['external_call_id'] ?? ($lastCall['external_call_id'] ?? 'Sem chamada ativa')) ?></strong>
                    <?php if ($isCallLive): ?>
                        <dl class="call-flow-details">
                            <dt>Webfone</dt><dd><?= h($originatingSip ?: 'Não configurado') ?></dd>
                            <dt>Destino</dt><dd><?= h($activeCall['destination_number']) ?></dd>
                            <dt>Bina</dt><dd><?= h($activeCall['origin_number'] ?: ($callConfig['origin_number'] ?? '-')) ?></dd>
                        </dl>
                        <small>Chamada direta pelo webfone SIP. Use Parar ligação para encerrar a chamada atual.</small>
                    <?php else: ?>
                        <small><?= h(($lastCall['status'] ?? 'pronto') . ($lastCall ? ' - ' . $lastCall['contato'] : '')) ?></small>
                    <?php endif; ?>
                </div>
                <?php if ($reserved && !$activeCall): ?>
                    <button class="button call" type="button" data-start-reserved-sip="<?= h($reserved['phone_e164']) ?>">Ligar</button>
                <?php elseif ($isCallLive): ?>
                    <div class="call-status"><span></span> Em ligação</div>
                    <div class="timer" data-start="<?= h($activeCall['answered_at'] ?: $activeCall['started_at']) ?>" data-live-call-timer>00:00</div>
                    <div class="button-row"><button class="button secondary" type="button">Silenciar</button><button class="button secondary" type="button">Espera</button><button class="button secondary" type="button">Transferir</button></div>
                    <form method="post" class="stack">
                        <input type="hidden" name="action" value="finish_call">
                        <input type="hidden" name="campaign_id" value="<?= $campaignId ?>">
                        <input type="hidden" name="call_id" value="<?= $activeCall['id'] ?>">
                        <label>Resultado<select name="result_id"><?php foreach ($results as $r): ?><option value="<?= $r['id'] ?>"><?= h($r['name']) ?></option><?php endforeach; ?></select></label>
                        <label>Observações<textarea name="notes" rows="4"></textarea></label>
                        <label>Agendar retorno<input name="callback_at" type="datetime-local"></label>
                        <label>Prioridade<select name="callback_priority"><option>normal</option><option>alta</option><option>urgente</option></select></label>
                        <button class="button">Concluir atendimento</button>
                    </form>
                <?php else: ?>
                    <p class="empty">Reserve um lead para iniciar a chamada.</p>
                <?php endif; ?>
                <?php endif; ?>
            </article>
        </section>
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Ultimas chamadas atendidas</h2>
                    <p>Historico recente deste consultor no discador.</p>
                </div>
            </div>
            <?php if (!$answeredCalls): ?>
                <p class="empty">Nenhuma chamada atendida registrada ainda.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Data</th><th>Contato</th><th>Telefone</th><th>Campanha</th><th>Duração</th><th>Resultado</th><th>Status</th><th>Ações</th></tr></thead>
                        <tbody data-answered-calls-body>
                        <?php foreach ($answeredCalls as $index => $call): ?>
                            <?php
                            $duration = call_conversation_duration_seconds($call);
                            $durationText = $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '00:00';
                            $answeredSeconds = $duration;
                            $isAnsweredCall = !empty($call['ever_answered']) && $answeredSeconds > 5;
                            ?>
                            <tr class="<?= $isAnsweredCall ? 'call-history-attended' : '' ?>">
                            <td><?= h(datetime_utc_display((string)($call['answered_at'] ?: $call['created_at']))) ?></td>
                                <td><?= h($call['contato'] ?: 'Sem nome') ?></td>
                                <?php
                                $phoneDigits = nvoip_phone_digits((string)$call['destination_number']);
                                if (strlen($phoneDigits) === 10 || strlen($phoneDigits) === 11) {
                                    $phoneDigits = '55' . $phoneDigits;
                                }
                                $whatsappLink = $phoneDigits !== '' ? 'https://wa.me/' . $phoneDigits : '';
                                ?>
                                <td>
                                    <span class="phone-inline-actions">
                                        <span><?= h($call['destination_number']) ?></span>
                                        <?php if ($whatsappLink): ?>
                                            <a class="mini-link whatsapp-link" href="<?= h($whatsappLink) ?>" target="_blank" rel="noopener">WhatsApp</a>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= h($call['campanha'] ?: '-') ?></td>
                                <td><?= h($durationText) ?></td>
                                <td><?= h($call['resultado']) ?></td>
                                <td>
                                    <?php if ($isAnsweredCall): ?>
                                        <span class="call-history-badge">Atendida +5s</span>
                                    <?php endif; ?>
                                    <?= h($call['status']) ?>
                                </td>
                                <td><button class="mini-link" type="button" data-open-call-history="<?= (int)$call['id'] ?>">Atendimento</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="answered-calls-more" data-answered-calls-more<?= $answeredCallsHasMore ? '' : ' hidden' ?>>
                    <button class="button secondary" type="button" data-load-more-answered-calls data-offset="15">Mostrar mais...</button>
                </div>
                <div data-answered-calls-modals>
                <?php foreach ($answeredCalls as $call): ?>
                    <?php
                    $duration = call_conversation_duration_seconds($call);
                    $durationText = $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '00:00';
                    $modalPhoneDigits = nvoip_phone_digits((string)$call['destination_number']);
                    if (strlen($modalPhoneDigits) === 10 || strlen($modalPhoneDigits) === 11) {
                        $modalPhoneDigits = '55' . $modalPhoneDigits;
                    }
                    $whatsappMessage = 'Oi, aqui é ' . (string)$user['name'] . ', da Ademicon, conforme combinado, vamos seguir nossa conversa por aqui ';
                    $modalWhatsappLink = $modalPhoneDigits !== '' ? 'https://wa.me/' . $modalPhoneDigits . '?text=' . rawurlencode($whatsappMessage) : '';
                    $callbackAtValue = !empty($call['callback_at']) ? datetime_local((string)$call['callback_at']) : '';
                    $callbackDisplay = '';
                    if (!empty($call['callback_at'])) {
                        try {
                            $callbackDisplay = datetime_utc_display((string)$call['callback_at'], 'd/m/Y H:i');
                        } catch (Throwable) {
                            $callbackDisplay = (string)$call['callback_at'];
                        }
                    }
                    $callbackPriority = (string)($call['callback_priority'] ?: 'normal');
                    $contactCustomFields = json_decode((string)($call['custom_json'] ?? ''), true);
                    $contactCustomFields = is_array($contactCustomFields) ? $contactCustomFields : [];
                    ?>
                    <section class="call-modal-backdrop is-hidden" data-call-history-modal="<?= (int)$call['id'] ?>">
                        <article class="call-modal">
                            <header>
                                <div>
                                    <span class="modal-kicker">Chamada atendida</span>
                                    <h2><?= h($call['contato'] ?: 'Cliente sem nome') ?></h2>
                                </div>
                                <div class="call-modal-actions">
                                    <strong><?= h($call['status']) ?></strong>
                                    <button type="button" class="icon-button" data-call-history-close aria-label="Fechar modal">x</button>
                                </div>
                            </header>
                            <div class="call-modal-grid">
                                <dl>
                                    <dt>Telefone</dt><dd><span class="modal-phone-actions"><?php if ($modalWhatsappLink): ?><a class="whatsapp-phone-link" href="<?= h($modalWhatsappLink) ?>" target="_blank" rel="noopener" title="Conversar pelo WhatsApp"><?= h($call['destination_number']) ?></a><?php else: ?><span><?= h($call['destination_number']) ?></span><?php endif; ?><button class="mini-link danger-link" type="button" data-quick-block-call="<?= (int)$call['id'] ?>">Bloquear</button></span></dd>
                                    <dt>E-mail</dt><dd><?= h($call['email'] ?: '-') ?></dd>
                                    <dt>Origem</dt><dd><?= h($call['contato_origem'] ?: '-') ?></dd>
                                    <dt>Cidade</dt><dd><?= h(trim((string)$call['city'] . ' / ' . (string)$call['state'], ' /') ?: '-') ?></dd>
                                    <dt>Produto</dt><dd><?= h($call['product'] ?: '-') ?></dd>
                                    <dt>Campanha</dt><dd><?= h($call['campanha'] ?: '-') ?></dd>
                                    <?php if (!empty($call['contato_observacoes'])): ?><dt>Observacao do contato</dt><dd><?= nl2br(h((string)$call['contato_observacoes'])) ?></dd><?php endif; ?>
                                    <?php if ($callbackDisplay): ?><dt>Retorno agendado</dt><dd><?= h($callbackDisplay) ?></dd><?php endif; ?>
                                    <?php if (!empty($call['callback_status'])): ?><dt>Status do retorno</dt><dd><?= h((string)$call['callback_status']) ?></dd><?php endif; ?>
                                    <?php if (!empty($call['callback_reason'])): ?><dt>Motivo do retorno</dt><dd><?= h((string)$call['callback_reason']) ?></dd><?php endif; ?>
                                    <?php foreach ($contactCustomFields as $customKey => $customValue): ?>
                                        <?php if (is_scalar($customValue) && trim((string)$customValue) !== ''): ?><dt><?= h((string)$customKey) ?></dt><dd><?= h((string)$customValue) ?></dd><?php endif; ?>
                                    <?php endforeach; ?>
                                </dl>
                                <div class="live-call-card is-live">
                                    <div class="live-indicator"><span></span> Atendimento registrado</div>
                                    <strong><?= h($durationText) ?></strong>
                                    <small><?= h($call['destination_number']) ?></small>
                                </div>
                            </div>
                            <form method="post" class="stack">
                                <input type="hidden" name="action" value="update_answered_call">
                                <input type="hidden" name="campaign_id" value="<?= $campaignId ?>">
                                <input type="hidden" name="call_id" value="<?= (int)$call['id'] ?>">
                                <label>Resultado<select name="result_id"><?php foreach ($results as $r): ?><option value="<?= $r['id'] ?>" <?= (int)$call['result_id'] === (int)$r['id'] ? 'selected' : '' ?>><?= h($r['name']) ?></option><?php endforeach; ?></select></label>
                                <label>Observações<textarea name="notes" rows="5"><?= h((string)$call['notes']) ?></textarea></label>
                                <label>Agendar retorno<input name="callback_at" type="datetime-local" value="<?= h($callbackAtValue) ?>"></label>
                                <label>Prioridade<select name="callback_priority"><option <?= $callbackPriority === 'normal' ? 'selected' : '' ?>>normal</option><option <?= $callbackPriority === 'alta' ? 'selected' : '' ?>>alta</option><option <?= $callbackPriority === 'urgente' ? 'selected' : '' ?>>urgente</option></select></label>
                                <button class="button" type="submit">Salvar atendimento</button>
                            </form>
                        </article>
                    </section>
                <?php endforeach; ?>
                </div>
                <div class="answered-calls-loading-overlay is-hidden" data-answered-calls-loading role="status" aria-live="polite" aria-label="Carregando chamadas">
                    <div class="answered-calls-loading-indicator"><span aria-hidden="true"></span><strong>Carregando chamadas...</strong></div>
                </div>
            <?php endif; ?>
        </section>
        <section class="webphone-panel" data-sip-floating data-auto-dialing="<?= $isAutoDialing ? '1' : '0' ?>"<?= $isAutoDialing && !$isBatchWaitingForWinner && !$activeCall && ($autoNextPhone !== '' || $reserved) ? ' data-auto-call-phone="' . h($autoNextPhone !== '' ? $autoNextPhone : (string)($reserved['phone_e164'] ?? '')) . '"' : '' ?><?= $isAutoDialing && $activeCall && $isCallLive && empty($activeCall['answered_at']) ? ' data-recover-auto-call-id="' . (int)$activeCall['id'] . '"' : '' ?>>
            <button class="webphone-launcher" type="button" data-webphone-toggle aria-label="Abrir webfone">&#10303;</button>
            <article class="webphone is-hidden" data-webphone>
                <header>
                    <div class="webphone-title"><span class="status-dot" data-floating-sip-dot></span><strong><?= $isAutoDialing ? 'Discador automatico' : 'Webfone manual' ?></strong></div>
                    <button type="button" class="icon-button" data-webphone-close aria-label="Fechar webfone">x</button>
                </header>
                <form class="webphone-form" data-floating-webphone-form>
                    <button type="button" class="phone-backspace" data-clear-phone aria-label="Limpar numero">&#9003;</button>
                    <input type="hidden" name="campaign_id" value="<?= $campaignId ?>">
                    <input name="manual_phone" class="dial-display" value="<?= h($isAutoDialing && $activeCall ? nvoip_phone_digits((string)$activeCall['destination_number']) : '') ?>" placeholder="Pesquisar ou digitar numero" inputmode="tel" autocomplete="off" data-phone-search-input>
                    <div class="webphone-tab-panel" data-tab-panel="recentes">
                        <div class="phone-subtabs">
                            <button type="button" class="active" data-phone-subtab="todas">Todas</button>
                            <button type="button" data-phone-subtab="recebidas">Recebidas</button>
                            <button type="button" data-phone-subtab="realizadas">Realizadas</button>
                            <button type="button" data-phone-subtab="perdidas">Perdidas</button>
                        </div>
                        <strong class="phone-history-day">Hoje</strong>
                        <div class="phone-subtab-panel active" data-subtab-panel="todas"><?php $renderPhoneHistory($recentCalls, 'Nenhuma ligacao recente.'); ?></div>
                        <div class="phone-subtab-panel" data-subtab-panel="recebidas"><?php $renderPhoneHistory($recentReceived, 'Nenhuma ligacao recebida registrada.'); ?></div>
                        <div class="phone-subtab-panel" data-subtab-panel="realizadas"><?php $renderPhoneHistory($recentMade, 'Nenhuma ligacao realizada.'); ?></div>
                        <div class="phone-subtab-panel" data-subtab-panel="perdidas"><?php $renderPhoneHistory($recentMissed, 'Nenhuma ligacao perdida.'); ?></div>
                    </div>
                    <div class="webphone-tab-panel" data-tab-panel="contatos">
                        <?php if (!$phoneContacts): ?>
                            <p class="phone-empty">Nenhum contato importado.</p>
                        <?php else: ?>
                            <?php foreach ($phoneContacts as $contact): ?>
                                <button type="button" class="phone-list-item" data-fill-phone="<?= h($contact['phone_e164']) ?>" data-phone-search="<?= h(strtolower(($contact['name'] ?? '') . ' ' . ($contact['phone_e164'] ?? '') . ' ' . ($contact['product'] ?? ''))) ?>">
                                    <span><strong><?= h($contact['name'] ?: 'Sem nome') ?></strong><small><?= h($contact['phone_e164']) ?></small></span>
                                    <em><?= h($contact['product'] ?: $contact['status']) ?></em>
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="webphone-tab-panel phone-keypad-panel active" data-tab-panel="teclado">
                    <div class="dialpad" data-dialpad>
                        <?php foreach ([['1',''], ['2','ABC'], ['3','DEF'], ['4','GHI'], ['5','JKL'], ['6','MNO'], ['7','PQRS'], ['8','TUV'], ['9','WXYZ'], ['*',''], ['0','+'], ['#','']] as $key): ?>
                            <button type="button" data-digit="<?= h($key[0]) ?>"><strong><?= h($key[0]) ?></strong><small><?= h($key[1]) ?></small></button>
                        <?php endforeach; ?>
                    </div>
                    <button class="call-fab <?= $isCallLive ? 'hangup' : '' ?>" type="submit" data-floating-call-button aria-label="<?= $isCallLive ? 'Encerrar chamada' : 'Ligar manualmente' ?>">&#9742;</button>
                    </div>
                    <div class="webphone-tab-panel" data-tab-panel="monitorar">
                        <div class="phone-monitor <?= $isCallLive ? 'online' : '' ?>">
                            <span></span>
                            <strong><?= $isBatchWaitingForWinner ? 'Discagem simultânea' : ($isCallLive ? 'Chamada ativa' : 'Sem chamada ativa') ?></strong>
                            <small><?= $isBatchWaitingForWinner ? 'Aguardando vencedora' : h($activeCall['external_call_id'] ?? ($lastCall['external_call_id'] ?? 'Aguardando discagem')) ?></small>
                        </div>
                        <dl class="phone-monitor-details">
                            <dt>Registro</dt><dd data-floating-register>Desconectado</dd>
                            <dt>Status</dt><dd data-floating-status><?= $isBatchWaitingForWinner ? 'aguardando_vencedora' : h($activeCall['status'] ?? ($lastCall['status'] ?? 'pronto')) ?></dd>
                            <dt>Destino</dt><dd data-floating-destination><?= $isBatchWaitingForWinner ? '-' : h($activeCall['destination_number'] ?? ($lastCall['destination_number'] ?? '-')) ?></dd>
                        </dl>
                        <audio data-floating-remote-audio autoplay></audio>
                    </div>
                </form>
                <footer>
                    <button type="button" data-phone-tab="recentes">Recentes</button>
                    <button type="button" data-phone-tab="contatos">Contatos</button>
                    <button type="button" class="active" data-phone-tab="teclado">Teclado</button>
                    <button type="button" data-phone-tab="monitorar">Monitorar</button>
                </footer>
            </article>
        </section>
        <?php
        $modalCall = $activeCall ? call_modal_payload((int)$activeCall['id'], (int)$user['company_id'], (int)$user['id']) : null;
        $liveWhatsappMessage = 'Oi, aqui é ' . (string)$user['name'] . ', da Ademicon, conforme combinado, vamos seguir nossa conversa por aqui ';
        $livePhoneDigits = nvoip_phone_digits((string)($modalCall['phone'] ?? $modalCall['destination_number'] ?? ''));
        if (strlen($livePhoneDigits) === 10 || strlen($livePhoneDigits) === 11) {
            $livePhoneDigits = '55' . $livePhoneDigits;
        }
        $liveWhatsappLink = $livePhoneDigits !== '' ? 'https://wa.me/' . $livePhoneDigits . '?text=' . rawurlencode($liveWhatsappMessage) : '';
        ?>
            <section class="call-modal-backdrop <?= $showAnsweredModal && $activeCall ? '' : 'is-hidden' ?>" data-call-modal data-whatsapp-message="<?= h($liveWhatsappMessage) ?>">
                <article class="call-modal">
                    <header>
                        <div>
                            <span class="modal-kicker">Ligação ativa</span>
                            <h2 data-live-lead-name><?= h((string)($modalCall['name'] ?? 'Cliente sem nome')) ?></h2>
                        </div>
                        <div class="call-modal-actions">
                            <strong data-live-call-status><?= h((string)($activeCall['status'] ?? '')) ?></strong>
                            <form method="post">
                                <input type="hidden" name="action" value="agent_status">
                                <input type="hidden" name="campaign_id" value="<?= $campaignId ?>">
                                <button name="status" value="Pausa" class="button secondary small" type="submit" data-pause-operation>Pausa</button>
                            </form>
                            <button type="button" class="icon-button" data-call-modal-close aria-label="Fechar modal">x</button>
                        </div>
                    </header>
                    <div class="call-modal-grid">
                        <dl>
                            <dt>Telefone</dt><dd><span class="modal-phone-actions"><a class="whatsapp-phone-link" data-live-whatsapp-link href="<?= h($liveWhatsappLink ?: '#') ?>" target="_blank" rel="noopener" title="Conversar pelo WhatsApp"<?= $liveWhatsappLink ? '' : ' hidden' ?>><?= h((string)($modalCall['phone'] ?? '-')) ?></a><button class="mini-link danger-link" type="button" data-quick-block-call="<?= (int)($activeCall['id'] ?? 0) ?>" data-live-block-button<?= $activeCall ? '' : ' hidden' ?>>Bloquear</button></span></dd>
                            <dt>Origem</dt><dd data-live-lead-origin><?= h((string)($modalCall['origin'] ?? '-')) ?></dd>
                            <dt>Cidade</dt><dd data-live-lead-city><?= h((string)($modalCall['city_state'] ?? '-')) ?></dd>
                            <dt>Produto</dt><dd data-live-lead-product><?= h((string)($modalCall['product'] ?? '-')) ?></dd>
                            <dt>Tentativas</dt><dd data-live-lead-attempts><?= h((string)($modalCall['attempts'] ?? 0)) ?></dd>
                        </dl>
                        <div class="live-call-card is-live">
                            <div class="live-indicator"><span></span> Em atendimento</div>
                            <strong data-live-call-external><?= h((string)($modalCall['external_call_id'] ?? 'Chamada ativa')) ?></strong>
                            <small data-live-call-destination><?= h((string)($modalCall['destination_number'] ?? '-')) ?></small>
                            <div class="timer" data-start="<?= h((string)($activeCall['answered_at'] ?? $activeCall['started_at'] ?? '')) ?>" data-live-call-timer>00:00</div>
                        </div>
                    </div>
                    <form method="post" class="stack" data-delayed-finish>
                        <input type="hidden" name="action" value="finish_call">
                        <input type="hidden" name="campaign_id" value="<?= $campaignId ?>">
                        <input type="hidden" name="call_id" value="<?= (int)($activeCall['id'] ?? 0) ?>" data-live-call-id>
                        <?php if ($isAutoDialing): ?><input type="hidden" name="continue_auto" value="1"><?php endif; ?>
                        <label>Resultado<select name="result_id"><?php foreach ($results as $r): ?><option value="<?= $r['id'] ?>"><?= h($r['name']) ?></option><?php endforeach; ?></select></label>
                        <label>Anotação da ligação<textarea name="notes" rows="5" placeholder="Registre o interesse, objeções e próximo passo"></textarea></label>
                        <label>Agendar retorno<input name="callback_at" type="datetime-local"></label>
                        <label>Prioridade<select name="callback_priority"><option>normal</option><option>alta</option><option>urgente</option></select></label>
                        <button class="button danger" type="submit" data-delayed-finish-button>Finalizar ligação</button>
                    </form>
                    <p class="hint" data-modal-countdown-text>Ao finalizar, a ligação ativa será encerrada imediatamente.</p>
                </article>
            </section>
    <?php });
}

function render_supervisor(): void
{
    layout('supervisor', function () {
        $user = current_user();
        [$userClause, $userParams] = scoped_users_clause('u', $user);
        [$callClause, $callParams] = scoped_calls_clause('co', $user);
        [$todayStartUtc, $todayEndUtc] = sao_paulo_utc_period_bounds('day');
        ?>
        <section class="metric-grid">
            <?php
            $metrics = [
                'Disponiveis' => one("SELECT COUNT(*) v FROM users u WHERE {$userClause} AND role = 'atendente' AND status = 'Disponivel'", $userParams)['v'],
                'Em ligacao' => one("SELECT COUNT(*) v FROM users u WHERE {$userClause} AND role = 'atendente' AND status = 'Em ligacao'", $userParams)['v'],
                'Em pausa' => one("SELECT COUNT(*) v FROM users u WHERE {$userClause} AND role = 'atendente' AND status LIKE '%Pausa%'", $userParams)['v'],
                'Chamadas em andamento' => one("SELECT COUNT(*) v FROM calls co WHERE {$callClause} AND co.status IN (" . live_call_statuses_sql() . ")", $callParams)['v'],
            ];
            foreach ($metrics as $label => $value): ?>
                <article class="metric"><span><?= h($label) ?></span><strong><?= h((string)$value) ?></strong></article>
            <?php endforeach; ?>
        </section>
        <?php if (is_account_admin($user)): ?>
            <section class="panel">
                <h2>Consultores</h2>
                <?= table(rows("SELECT u.name consultor, COALESCE(t.name, '-') equipe, u.status, COALESCE(ca.name, '-') campanha, COUNT(co.id) chamadas_dia
                FROM users u
                LEFT JOIN teams t ON t.id = u.team_id
                LEFT JOIN calls co ON co.agent_id = u.id AND co.company_id = u.company_id AND co.created_at >= ? AND co.created_at < ?
                LEFT JOIN campaigns ca ON ca.id = co.campaign_id AND ca.company_id = u.company_id
                WHERE {$userClause} AND u.role = 'atendente'
                GROUP BY u.id
                ORDER BY u.status, u.name", array_merge([$todayStartUtc, $todayEndUtc], $userParams)), ['consultor', 'equipe', 'status', 'campanha', 'chamadas_dia']) ?>
            </section>
        <?php endif; ?>
        <details class="panel import-history-disclosure report-disclosure">
            <summary><span>Chamadas em tempo real</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="import-history-content">
            <?= table(rows("SELECT co.status, ct.name contato, u.name consultor, ca.name campanha, co.started_at, co.destination_number FROM calls co JOIN contacts ct ON ct.id = co.contact_id AND ct.company_id = co.company_id LEFT JOIN users u ON u.id = co.agent_id AND u.company_id = co.company_id LEFT JOIN campaigns ca ON ca.id = co.campaign_id AND ca.company_id = co.company_id WHERE {$callClause} ORDER BY co.id DESC LIMIT 12", $callParams), ['status', 'contato', 'consultor', 'campanha', 'started_at', 'destination_number']) ?>
            </div>
        </details>
        <?php render_recordings_content(); ?>
    <?php });
}

function render_reports(): void
{
    layout('reports', function () {
        $reportCostMicros = call_cost_sql('co');
        [$campaignClause, $campaignParams] = tenant_clause('ca');
        $campaigns = rows("SELECT ca.id, ca.name FROM campaigns ca WHERE {$campaignClause} ORDER BY ca.name", $campaignParams);
        $selectedCampaignId = (int)($_GET['campaign_id'] ?? ($campaigns[0]['id'] ?? 0));
        if ($selectedCampaignId <= 0 && $campaigns) {
            $selectedCampaignId = (int)$campaigns[0]['id'];
        }
        $selectedStatuses = is_array($_GET['status'] ?? null) ? array_values(array_filter(array_map('strval', $_GET['status'] ?? []))) : [];
        $logFilters = [
            'status' => $selectedStatuses,
            'phone' => trim((string)($_GET['phone'] ?? '')),
            'from' => trim((string)($_GET['from'] ?? '')),
            'to' => trim((string)($_GET['to'] ?? '')),
        ];
        [$reportClause, $reportParams] = report_call_filter_clause('co', 'ct', $selectedCampaignId, $logFilters);
        $logPage = max(1, (int)($_GET['logs_page'] ?? 1));
        $campaignLogs = $selectedCampaignId > 0 ? campaign_call_logs_page($selectedCampaignId, $logFilters, $logPage, 10) : ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 10, 'pages' => 1];
        $statusLabels = call_attempt_status_labels();
        $baseQuery = $_GET;
        unset($baseQuery['logs_page']);
        $baseQueryString = http_build_query($baseQuery);
        ?>
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Filtros dos relatorios</h2>
                    <p>Os filtros por campanha, status, telefone e periodo se aplicam a todas as amostragens abaixo.</p>
                </div>
            </div>
            <form method="get" class="form-grid campaign-log-filters">
                <input type="hidden" name="page" value="reports">
                <label>Campanha
                    <select name="campaign_id">
                        <?php foreach ($campaigns as $campaign): ?>
                            <option value="<?= (int)$campaign['id'] ?>" <?= $selectedCampaignId === (int)$campaign['id'] ? 'selected' : '' ?>><?= h((string)$campaign['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Telefone
                    <input name="phone" value="<?= h($logFilters['phone']) ?>" placeholder="Filtre por numero">
                </label>
                <label>De
                    <input name="from" type="date" value="<?= h($logFilters['from']) ?>">
                </label>
                <label>Ate
                    <input name="to" type="date" value="<?= h($logFilters['to']) ?>">
                </label>
                <label class="wide">Status
                    <div class="status-chip-list">
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <label class="check status-chip">
                                <input type="checkbox" name="status[]" value="<?= h($key) ?>" <?= in_array($key, $selectedStatuses, true) ? 'checked' : '' ?>>
                                <span><?= h($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </label>
                <button class="button">Aplicar filtros</button>
            </form>
            <?php if (!$campaignLogs['rows']): ?>
                <p class="empty">Nenhuma chamada encontrada para a campanha filtrada.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Data</th><th>Campanha</th><th>Contato</th><th>Telefone</th><th>Consultor</th><th>Tentativa</th><th>Status bruto</th><th>Status interno</th><th>Duracao</th><th>Erro</th></tr></thead>
                        <tbody>
                        <?php foreach ($campaignLogs['rows'] as $log): ?>
                            <?php $duration = call_conversation_duration_seconds($log); ?>
                            <tr>
                                <td><?= h(datetime_utc_display((string)($log['created_at'] ?? ''))) ?></td>
                                <td><?= h((string)($log['campanha'] ?? '-')) ?></td>
                                <td><?= h((string)($log['contato'] ?? '-')) ?></td>
                                <td><?= h((string)($log['destination_number'] ?? '-')) ?></td>
                                <td><?= h((string)($log['consultor'] ?? '-')) ?></td>
                                <td><?= h((string)($log['attempt_number'] ?? '1')) ?></td>
                                <td><?= h((string)($log['provider_status_raw'] ?: '-')) ?></td>
                                <td><?= h((string)($log['internal_status'] ?: normalize_call_attempt_status((string)($log['status'] ?? ''), ['answered_at' => $log['answered_at'] ?? null, 'ended_at' => $log['ended_at'] ?? null, 'duration_seconds' => $duration]))) ?></td>
                                <td><?= h($duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '-') ?></td>
                                <td><?= h((string)($log['error_message'] ?: '-')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($campaignLogs['pages'] > 1): ?>
                    <div class="pagination table-pagination" aria-label="Paginacao dos logs de chamadas">
                        <?php if ($campaignLogs['page'] > 1): ?>
                            <a class="button secondary" href="?<?= h($baseQueryString . ($baseQueryString !== '' ? '&' : '') . 'logs_page=' . max(1, $campaignLogs['page'] - 1)) ?>">Anterior</a>
                        <?php endif; ?>
                        <span>Página <?= h((string)$campaignLogs['page']) ?> de <?= h((string)$campaignLogs['pages']) ?></span>
                        <?php if ($campaignLogs['page'] < $campaignLogs['pages']): ?>
                            <a class="button secondary" href="?<?= h($baseQueryString . ($baseQueryString !== '' ? '&' : '') . 'logs_page=' . min($campaignLogs['pages'], $campaignLogs['page'] + 1)) ?>">Proxima</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        <section class="stack">
            <details class="panel import-history-disclosure report-disclosure">
                <summary><span>Ligações detalhadas</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
                <div class="import-history-content">
                <?= table(rows("SELECT co.created_at data, ct.name contato, co.destination_number telefone, u.name consultor, ca.name campanha, ROUND(co.billable_seconds / 60.0, 1) minutos, COALESCE(cr.name, '-') resultado, co.status, printf('%.2f', ({$reportCostMicros}) / 1000000.0) custo
                    FROM calls co
                    JOIN contacts ct ON ct.id = co.contact_id
                    LEFT JOIN users u ON u.id = co.agent_id
                    LEFT JOIN campaigns ca ON ca.id = co.campaign_id
                    LEFT JOIN call_results cr ON cr.id = co.result_id
                    WHERE {$reportClause}
                    ORDER BY co.id DESC LIMIT 50", $reportParams), ['data', 'contato', 'telefone', 'consultor', 'campanha', 'minutos', 'resultado', 'status', 'custo']) ?>
                </div>
            </details>
            <?php if (is_platform_admin()): ?>
            <details class="panel import-history-disclosure report-disclosure">
                <summary><span>Produtividade por consultor</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
                <div class="import-history-content">
                <?= table(rows("SELECT u.name consultor, COUNT(co.id) ligacoes, SUM(CASE WHEN co.status = 'completed' THEN 1 ELSE 0 END) concluidas, ROUND(COALESCE(SUM(co.billable_seconds), 0) / 60.0, 1) minutos, printf('%.2f', COALESCE(SUM({$reportCostMicros}), 0) / 1000000.0) custo
                    FROM users u
                    JOIN calls co ON co.agent_id = u.id
                    LEFT JOIN contacts ct ON ct.id = co.contact_id
                    WHERE {$reportClause}
                    GROUP BY u.id
                    ORDER BY ligacoes DESC", $reportParams), ['consultor', 'ligacoes', 'concluidas', 'minutos', 'custo']) ?>
                </div>
            </details>
            <?php endif; ?>
        </section>
        <section class="stack">
            <details class="panel import-history-disclosure report-disclosure">
                <summary><span>Por dia</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
                <div class="import-history-content">
                <?= table(rows("SELECT strftime('%d/%m/%Y 00:00:00', ligflow_local_datetime(co.created_at)) dia, date(ligflow_local_datetime(co.created_at)) sort_key, COUNT(*) ligacoes, ROUND(SUM(co.billable_seconds) / 60.0, 1) minutos, printf('%.2f', SUM({$reportCostMicros}) / 1000000.0) custo FROM calls co LEFT JOIN contacts ct ON ct.id = co.contact_id WHERE {$reportClause} GROUP BY sort_key ORDER BY sort_key DESC LIMIT 31", $reportParams), ['dia', 'ligacoes', 'minutos', 'custo']) ?>
                </div>
            </details>
            <details class="panel import-history-disclosure report-disclosure">
                <summary><span>Por mes</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
                <div class="import-history-content">
                <?= table(rows("SELECT strftime('%m/%Y', ligflow_local_datetime(co.created_at)) mes, strftime('%Y-%m', ligflow_local_datetime(co.created_at)) sort_key, COUNT(*) ligacoes, ROUND(SUM(co.billable_seconds) / 60.0, 1) minutos, printf('%.2f', SUM({$reportCostMicros}) / 1000000.0) custo FROM calls co LEFT JOIN contacts ct ON ct.id = co.contact_id WHERE {$reportClause} GROUP BY sort_key ORDER BY sort_key DESC LIMIT 24", $reportParams), ['mes', 'ligacoes', 'minutos', 'custo']) ?>
                </div>
            </details>
        </section>
        <section class="stack">
            <details class="panel import-history-disclosure report-disclosure">
                <summary><span>Por hora</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
                <div class="import-history-content">
                <?= table(rows("SELECT strftime('%d/%m/%Y %H:00:00', ligflow_local_datetime(co.created_at)) hora, strftime('%Y-%m-%d %H', ligflow_local_datetime(co.created_at)) sort_key, COUNT(*) ligacoes, ROUND(SUM(co.billable_seconds) / 60.0, 1) minutos, printf('%.2f', SUM({$reportCostMicros}) / 1000000.0) custo FROM calls co LEFT JOIN contacts ct ON ct.id = co.contact_id WHERE {$reportClause} GROUP BY sort_key ORDER BY sort_key DESC LIMIT 48", $reportParams), ['hora', 'ligacoes', 'minutos', 'custo']) ?>
                </div>
            </details>
            <details class="panel import-history-disclosure report-disclosure">
                <summary><span>Por ano</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
                <div class="import-history-content">
                <?= table(rows("SELECT strftime('%Y', ligflow_local_datetime(co.created_at)) ano, COUNT(*) ligacoes, ROUND(SUM(co.billable_seconds) / 60.0, 1) minutos, printf('%.2f', SUM({$reportCostMicros}) / 1000000.0) custo FROM calls co LEFT JOIN contacts ct ON ct.id = co.contact_id WHERE {$reportClause} GROUP BY ano ORDER BY ano DESC", $reportParams), ['ano', 'ligacoes', 'minutos', 'custo']) ?>
                </div>
            </details>
        </section>
        <details class="panel import-history-disclosure report-disclosure">
            <summary><span>Campanhas</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="import-history-content">
            <?= table(rows("SELECT ca.name campanha, COUNT(DISTINCT ct.id) leads, COUNT(DISTINCT CASE WHEN ct.status = 'concluido' THEN ct.id END) trabalhados, COUNT(DISTINCT CASE WHEN ct.status IN ('novo','retentar') THEN ct.id END) restantes, printf('%.2f', COALESCE(SUM({$reportCostMicros}), 0) / 1000000.0) custo
                FROM campaigns ca
                JOIN calls co ON co.campaign_id = ca.id
                LEFT JOIN contacts ct ON ct.id = co.contact_id
                WHERE {$reportClause}
                GROUP BY ca.id
                ORDER BY ca.id DESC", $reportParams), ['campanha', 'leads', 'trabalhados', 'restantes', 'custo']) ?>
            </div>
        </details>
    <?php });
}

function render_recordings_content(): void
{
        $user = current_user();
        backfill_recordings_from_call_events((int)$user['company_id']);
        [$clause, $params] = scoped_calls_clause('co', $user);
        $recordingPhone = preg_replace('/\D+/', '', trim((string)($_GET['recording_phone'] ?? ''))) ?: '';
        $recordingName = trim((string)($_GET['recording_name'] ?? ''));
        $recordingDate = trim((string)($_GET['recording_date'] ?? ''));
        $recordingTimezone = (string)(one('SELECT timezone FROM companies WHERE id = ?', [(int)$user['company_id']])['timezone'] ?? 'America/Sao_Paulo');
        try { $recordingOffset = (new DateTimeZone($recordingTimezone))->getOffset(new DateTimeImmutable('now')); } catch (Throwable) { $recordingOffset = -10800; }
        $recordingFiltersActive = $recordingPhone !== '' || $recordingName !== '' || $recordingDate !== '';
        $recordingWhere = [$clause];
        if ($recordingPhone !== '') {
            $recordingWhere[] = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(co.destination_number, '+', ''), '(', ''), ')', ''), '-', ''), ' ', '') LIKE ?";
            $params[] = '%' . $recordingPhone . '%';
        }
        if ($recordingName !== '') {
            $recordingWhere[] = 'LOWER(COALESCE(ct.name, \'\')) LIKE LOWER(?)';
            $params[] = '%' . $recordingName . '%';
        }
        if ($recordingDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $recordingDate)) {
            $recordingWhere[] = "date(datetime(co.created_at, '" . (int)$recordingOffset . " seconds')) = ?";
            $params[] = $recordingDate;
        }
        $recordings = rows("SELECT co.id, co.created_at data, co.answered_at, co.ended_at, ct.name contato, u.name consultor, ca.name campanha,
                   co.destination_number telefone, co.duration_seconds, co.recording_url, COALESCE(cr.name, '-') resultado, co.status
            FROM calls co
            JOIN contacts ct ON ct.id = co.contact_id
            LEFT JOIN campaigns ca ON ca.id = co.campaign_id
            LEFT JOIN users u ON u.id = co.agent_id
            LEFT JOIN call_results cr ON cr.id = co.result_id
            WHERE " . implode(' AND ', $recordingWhere) . "
            ORDER BY co.id DESC LIMIT 100", $params);
        $webhookLogs = [];
        if (is_platform_admin($user)) {
            [$logClause, $logParams] = tenant_clause('wl');
            $webhookLogs = rows("SELECT wl.created_at, wl.status, wl.recording_url, wl.match_key, wl.call_id
            FROM nvoip_webhook_logs wl
            WHERE {$logClause}
            ORDER BY wl.id DESC
            LIMIT 8", $logParams);
        }
        $config = nvoip_config((int)$user['company_id']);
        $webhookUrl = (string)($config['webhook_url'] ?: 'http://localhost/voipCalutec/?page=nvoip_webhook');
        $webhookIsLocal = preg_match('~https?://(localhost|127\.0\.0\.1)~i', $webhookUrl) === 1;
        ?>
        <?php if (is_platform_admin() && $webhookIsLocal): ?>
            <div class="flash error">O webhook da Nvoip esta configurado como localhost. A Nvoip nao consegue enviar gravacoes para um endereco local; use uma URL publica HTTPS apontando para <?= h('?page=nvoip_webhook') ?>.</div>
        <?php endif; ?>
        <?php if (is_platform_admin()): ?>
        <details class="panel import-history-disclosure report-disclosure">
            <summary><span>Retornos da Nvoip</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="import-history-content">
            <div class="section-head">
                <div>
                    <p>Ultimos webhooks recebidos com status, identificador e URL de gravacao quando enviada.</p>
                </div>
            </div>
            <?php if (!$webhookLogs): ?>
                <p class="empty">Nenhum webhook da Nvoip recebido ainda. Se ha gravacoes no painel da Nvoip, confira se o webhook publico esta configurado na conta.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Recebido em</th><th>Status</th><th>Chamada</th><th>Identificador</th><th>Gravacao</th></tr></thead>
                        <tbody>
                        <?php foreach ($webhookLogs as $log): ?>
                            <tr>
                                <td><?= h(datetime_utc_display((string)$log['created_at'])) ?></td>
                                <td><?= h($log['status'] ?: '-') ?></td>
                                <td><?= h($log['call_id'] ? '#' . $log['call_id'] : 'Nao vinculada') ?></td>
                                <td><?= h($log['match_key'] ?: '-') ?></td>
                                <td><?= h($log['recording_url'] ? 'URL recebida' : 'Sem URL') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            </div>
        </details>
        <?php endif; ?>
        <details class="panel import-history-disclosure report-disclosure" id="gravacoes" <?= $recordingFiltersActive ? 'open' : '' ?>>
            <summary><span>Gravacoes das ligacoes</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="import-history-content">
            <div class="section-head">
                <div>
                    <p>Ouça e baixe as gravacoes recebidas pela Nvoip. As chamadas recentes ficam como pendentes ate o webhook informar a URL do audio.</p>
                </div>
            </div>
            <form method="get" class="form-grid recording-filters">
                <input type="hidden" name="page" value="supervisor">
                <label>Telefone<input name="recording_phone" inputmode="tel" value="<?= h((string)($_GET['recording_phone'] ?? '')) ?>" placeholder="Digite o telefone"></label>
                <label>Nome<input name="recording_name" value="<?= h($recordingName) ?>" placeholder="Nome do contato"></label>
                <label>Data<input name="recording_date" type="date" value="<?= h($recordingDate) ?>"></label>
                <div class="button-row"><button class="button" type="submit">Buscar</button><?php if ($recordingFiltersActive): ?><a class="button secondary" href="?page=supervisor#gravacoes">Limpar</a><?php endif; ?></div>
            </form>
            <?php if (!$recordings): ?>
                <p class="empty"><?= $recordingFiltersActive ? 'Nenhuma gravacao corresponde aos filtros informados.' : 'Nenhuma chamada registrada ainda.' ?></p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Data</th><th>Contato</th><th>Telefone</th><th>Consultor</th><th>Campanha</th><th>Duracao</th><th>Resultado</th><th>Gravacao</th></tr></thead>
                        <tbody>
                        <?php foreach ($recordings as $call): ?>
                            <?php
                            $hasRecording = !empty($call['recording_url']) && preg_match('~^https?://~i', (string)$call['recording_url']);
                            $duration = call_conversation_duration_seconds($call);
                            $durationText = $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '-';
                            ?>
                            <tr>
                                <td><?= h($call['data']) ?></td>
                                <td><?= h($call['contato'] ?: 'Contato sem nome') ?></td>
                                <td><?= h($call['telefone']) ?></td>
                                <td><?= h($call['consultor'] ?: '-') ?></td>
                                <td><?= h($call['campanha'] ?: '-') ?></td>
                                <td><?= h($durationText) ?></td>
                                <td><?= h($call['resultado']) ?></td>
                                <td>
                                    <button class="recording-action <?= $hasRecording ? 'is-ready' : '' ?>" type="button" data-open-recording="<?= (int)$call['id'] ?>" title="<?= $hasRecording ? 'Ouvir gravacao' : 'Gravacao pendente' ?>">
                                        <span><?= $hasRecording ? '&#9679;' : '&#9711;' ?></span>
                                        <?= $hasRecording ? 'Ouvir' : 'Pendente' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php foreach ($recordings as $call): ?>
                    <?php
                    $hasRecording = !empty($call['recording_url']) && preg_match('~^https?://~i', (string)$call['recording_url']);
                    $duration = call_conversation_duration_seconds($call);
                    $durationText = $duration > 0 ? gmdate($duration >= 3600 ? 'H:i:s' : 'i:s', $duration) : '-';
                    $playUrl = '?page=recording_file&id=' . (int)$call['id'];
                    $downloadUrl = $playUrl . '&download=1';
                    ?>
                    <section class="call-modal-backdrop is-hidden" data-recording-modal="<?= (int)$call['id'] ?>">
                        <article class="call-modal recording-modal">
                            <header>
                                <div>
                                    <span class="modal-kicker">Gravacao da ligacao</span>
                                    <h2><?= h($call['contato'] ?: 'Contato sem nome') ?></h2>
                                    <p><?= h($call['telefone']) ?></p>
                                </div>
                                <div class="call-modal-actions">
                                    <span class="status-badge <?= $hasRecording ? 'called' : '' ?>"><?= $hasRecording ? 'Disponivel' : 'Pendente' ?></span>
                                    <button type="button" class="icon-button" data-recording-close aria-label="Fechar modal">x</button>
                                </div>
                            </header>
                            <div class="call-modal-grid">
                                <dl>
                                    <dt>Data</dt><dd><?= h($call['data']) ?></dd>
                                    <dt>Consultor</dt><dd><?= h($call['consultor'] ?: '-') ?></dd>
                                    <dt>Campanha</dt><dd><?= h($call['campanha'] ?: '-') ?></dd>
                                    <dt>Duracao</dt><dd><?= h($durationText) ?></dd>
                                    <dt>Resultado</dt><dd><?= h($call['resultado']) ?></dd>
                                    <dt>Status</dt><dd><?= h($call['status']) ?></dd>
                                </dl>
                                <div class="recording-player">
                                    <?php if ($hasRecording): ?>
                                        <audio controls preload="none" src="<?= h($playUrl) ?>"></audio>
                                        <div class="button-row">
                                            <a class="button secondary" href="<?= h($downloadUrl) ?>">Baixar</a>
                                            <a class="mini-link" href="<?= h((string)$call['recording_url']) ?>" target="_blank" rel="noopener">Abrir origem</a>
                                        </div>
                                    <?php else: ?>
                                        <p class="empty">Aguardando a Nvoip enviar a gravacao pelo webhook.</p>
                                        <small><?= h($call['status']) ?> - <?= h($call['data']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </details>
    <?php
}

function render_recordings(): void
{
    redirect('?page=supervisor#gravacoes');
}

function render_costs(): void
{
    layout('costs', function () {
        $user = current_user();
        $companyId = (int)$user['company_id'];
        $telephony = telephony_credit_state($companyId);
        $subscription = one('SELECT s.*,p.monthly_price,p.billing_period,p.description FROM subscriptions s LEFT JOIN plans p ON p.id=s.plan_id WHERE s.company_id=?', [$companyId]) ?: [];
        $currentPeriod = $telephony['period_id'] > 0
            ? one('SELECT * FROM subscription_periods WHERE id=? AND company_id=?', [$telephony['period_id'], $companyId])
            : null;
        $cycleStart = (string)($currentPeriod['starts_at'] ?? $subscription['starts_at'] ?? '');
        $cycleEnd = (string)($currentPeriod['ends_at'] ?? $subscription['renews_at'] ?? '');
        $cycleStartInput = strlen($cycleStart) === 10 ? $cycleStart . ' 00:00:00' : $cycleStart;
        $cycleEndInput = strlen($cycleEnd) === 10 ? $cycleEnd . ' 00:00:00' : $cycleEnd;
        $cycleStartUtc = local_datetime_to_utc_storage($cycleStartInput);
        $cycleEndUtc = local_datetime_to_utc_storage($cycleEndInput);
        $cycleStartDisplay = $cycleStartUtc !== '' ? datetime_utc_display($cycleStartUtc) : date_br_display($cycleStart);
        $cycleEndDisplay = $cycleEndUtc !== '' ? datetime_utc_display($cycleEndUtc) : date_br_display($cycleEnd);
        $costMicrosSql = call_cost_sql('co');
        $billedMinutesSql = "CASE WHEN co.billable_seconds > 0 THEN CAST((co.billable_seconds + 59) / 60 AS INTEGER) ELSE 0 END";
        $cycleWhere = ['co.company_id=?'];
        $cycleParams = [$companyId];
        if ($telephony['period_id'] > 0 && $cycleStartUtc !== '' && $cycleEndUtc !== '') {
            $cycleWhere[] = '(co.telephony_period_id=? OR (co.telephony_period_id IS NULL AND co.created_at>=? AND co.created_at<?))';
            array_push($cycleParams, $telephony['period_id'], $cycleStartUtc, $cycleEndUtc);
        } elseif ($cycleStartUtc !== '' && $cycleEndUtc !== '') {
            $cycleWhere[] = 'co.created_at>=? AND co.created_at<?';
            array_push($cycleParams, $cycleStartUtc, $cycleEndUtc);
        }
        $total = one("SELECT COUNT(*) ligacoes, COALESCE(SUM(co.billable_seconds), 0) segundos, ROUND(COALESCE(SUM(co.billable_seconds), 0) / 60.0, 1) minutos, COALESCE(SUM({$billedMinutesSql}), 0) minutos_tarifados, COALESCE(SUM({$costMicrosSql}), 0) custo_micros FROM calls co WHERE " . implode(' AND ', $cycleWhere), $cycleParams) ?: [];
        $usage = monthly_usage($companyId, (float)($total['segundos'] ?? 0));
        $telephonyLedger = $telephony['period_id'] > 0
            ? rows('SELECT l.*, u.name responsavel FROM telephony_ledger l LEFT JOIN users u ON u.id=l.responsible_user_id WHERE l.company_id=? AND l.subscription_period_id=? ORDER BY l.id DESC LIMIT 50', [$companyId, $telephony['period_id']])
            : [];
        $telephonyDebitedUsage = $telephony['period_id'] > 0
            ? (one("SELECT
                    COALESCE(SUM(-l.amount_micros), 0) consumed_micros,
                    COALESCE(SUM(
                        CASE
                            WHEN COALESCE(NULLIF(co.billing_rate_micros, 0), NULLIF(sp.telephony_rate_micros, 0)) IS NOT NULL
                            THEN CAST(-l.amount_micros AS REAL) / COALESCE(NULLIF(co.billing_rate_micros, 0), NULLIF(sp.telephony_rate_micros, 0))
                            ELSE 0
                        END
                    ), 0) billed_minutes
                FROM telephony_ledger l
                LEFT JOIN calls co ON co.id=l.call_id AND co.company_id=l.company_id
                LEFT JOIN subscription_periods sp ON sp.id=l.subscription_period_id AND sp.company_id=l.company_id
                WHERE l.company_id=? AND l.subscription_period_id=? AND l.entry_type='CALL_DEBIT' AND l.amount_micros < 0", [$companyId, $telephony['period_id']]) ?: [])
            : [];
        $telephonyConsumedMicros = (int)($telephonyDebitedUsage['consumed_micros'] ?? 0);
        $telephonyBilledMinutes = (float)($telephonyDebitedUsage['billed_minutes'] ?? 0);
        $billing = tenant_billing_state($companyId);
        $payments = rows('SELECT * FROM payments WHERE company_id=? ORDER BY id DESC LIMIT 30', [$companyId]);
        $tenantTimezone = (string)(one('SELECT timezone FROM companies WHERE id=?', [$companyId])['timezone'] ?? 'America/Sao_Paulo');
        $selectedPayment = !empty($_GET['payment_id']) ? one('SELECT * FROM payments WHERE id=? AND company_id=?', [(int)$_GET['payment_id'],$companyId]) : null;
        $paymentConfig = mercado_pago_config();
        $checkout = $selectedPayment ? (json_decode((string)$selectedPayment['checkout_data_json'], true) ?: []) : [];
        $showRenewalCallout = !is_platform_admin() && in_array((string)$billing['state'], ['warning', 'overdue', 'blocked'], true);
        ?>
        <section class="panel billing-plan-summary<?= $showRenewalCallout ? ' needs-renewal' : '' ?>">
            <div class="section-head"><div><h2><?= h((string)($subscription['plan_name'] ?? 'Plano nao definido')) ?></h2><p><?= h((string)($subscription['description'] ?? 'Assinatura LigFlow')) ?></p></div><strong><?= money((float)($subscription['monthly_price'] ?? 0)) ?> / <?= h((string)($subscription['billing_period'] ?? 'Mensal')) ?></strong></div>
            <dl class="billing-details">
                <dt>Inicio do ciclo</dt><dd><?= h($cycleStartDisplay) ?></dd>
                <dt>Fim do ciclo</dt><dd><?= h($cycleEndDisplay) ?></dd>
                <dt>Status</dt><dd><?= h(strtoupper((string)$billing['state'])) ?></dd>
                <dt>Credito inicial do ciclo</dt><dd><?= $telephony['configured'] ? h(billing_micros_to_brl($telephony['initial_micros'])) : 'Nao configurado' ?></dd>
                <dt>Tarifa vigente</dt><dd><?= $telephony['configured'] ? h(billing_micros_to_brl($telephony['rate_micros'])) . ' / min' : 'Nao configurada' ?></dd>
                <dt>Saldo de telefonia</dt><dd><?= $telephony['configured'] ? h(billing_micros_to_brl($telephony['balance_micros'])) : 'Configure e renove o plano' ?></dd>
            </dl>
            <?php if ($showRenewalCallout): ?>
                <div class="billing-renewal-callout" role="status">
                    <div><strong><?= $billing['state'] === 'warning' ? 'Renovacao proxima' : ($billing['state'] === 'blocked' ? 'Plano bloqueado' : 'Renovacao pendente') ?></strong><span><?= h((string)$billing['message']) ?></span></div>
                    <a class="button" href="#pagamento">Renovar plano agora</a>
                </div>
            <?php endif; ?>
        </section>
        <section class="metric-grid">
            <article class="metric"><span>Minutos usados no ciclo</span><strong><?= h(number_format((float)$usage['used'], 1, ',', '.')) ?></strong></article>
            <article class="metric"><span>Ligações no ciclo</span><strong><?= h((string)($total['ligacoes'] ?? 0)) ?></strong></article>
            <article class="metric"><span>Minutos tarifados no ciclo</span><strong><?= h(number_format($telephonyBilledMinutes, 1, ',', '.')) ?></strong></article>
            <article class="metric"><span>Credito consumido no ciclo</span><strong><?= $telephony['configured'] ? h(billing_micros_to_brl($telephonyConsumedMicros)) : '-' ?></strong></article>
            <article class="metric"><span>Saldo de telefonia</span><strong><?= $telephony['configured'] ? h(billing_micros_to_brl($telephony['balance_micros'])) : '-' ?></strong></article>
        </section>
        <section class="panel">
            <div class="section-head"><div><h2>Historico financeiro de telefonia</h2><p>Creditos, debitos de chamadas, estornos e ajustes deste ciclo.</p></div></div>
            <?php if (!$telephonyLedger): ?>
                <p class="empty">Nenhum lancamento de telefonia neste tenant.</p>
            <?php else: ?>
                <div class="table-wrap"><table><thead><tr><th>Data</th><th>Tipo</th><th>Valor</th><th>Saldo anterior</th><th>Saldo posterior</th><th>Referencia</th><th>Responsavel</th></tr></thead><tbody>
                <?php foreach ($telephonyLedger as $entry): ?>
                    <tr><td><?= h(date_br_display((string)$entry['created_at'])) ?></td><td><?= h((string)$entry['entry_type']) ?></td><td><?= h(billing_micros_to_brl((int)$entry['amount_micros'])) ?></td><td><?= h(billing_micros_to_brl((int)$entry['balance_before_micros'])) ?></td><td><?= h(billing_micros_to_brl((int)$entry['balance_after_micros'])) ?></td><td><?= h((string)($entry['reference_type'] ?: '-')) ?><?= $entry['reference_id'] ? ':' . (int)$entry['reference_id'] : '' ?></td><td><?= h((string)($entry['responsavel'] ?: '-')) ?></td></tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </section>
        <?php if (is_platform_admin()): ?>
            <?php $adjustmentCompanies = rows('SELECT id, trade_name FROM companies ORDER BY trade_name'); ?>
            <section class="panel">
                <h2>Ajuste manual de credito</h2>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="adjust_telephony_credit">
                    <label>Cliente<select name="company_id" required><?php foreach ($adjustmentCompanies as $adjustmentCompany): ?><option value="<?= (int)$adjustmentCompany['id'] ?>"><?= h((string)$adjustmentCompany['trade_name']) ?></option><?php endforeach; ?></select></label>
                    <label>Tipo<select name="entry_type"><option value="MANUAL_CREDIT">Adicionar credito</option><option value="MANUAL_DEBIT">Debitar credito</option><option value="REFUND">Estornar debito</option></select></label>
                    <label>Valor<input name="amount" type="number" min="0.000001" step="0.000001" required></label>
                    <label>Observacao<input name="notes" maxlength="500"></label>
                    <button class="button" type="submit">Salvar ajuste</button>
                </form>
            </section>
        <?php endif; ?>
        <section class="<?= is_platform_admin() ? 'grid two' : '' ?>">
            <?php if (is_platform_admin()): ?>
            <article class="panel">
                <h2>Gasto por consultor</h2>
                <?= table(rows("SELECT u.name consultor, COUNT(co.id) ligacoes, ROUND(COALESCE(SUM(co.billable_seconds), 0) / 60.0, 1) minutos, printf('%.2f', COALESCE(SUM({$costMicrosSql}), 0) / 1000000.0) custo FROM users u LEFT JOIN calls co ON co.agent_id = u.id WHERE u.company_id = ? GROUP BY u.id ORDER BY COALESCE(SUM({$costMicrosSql}), 0) DESC", [current_user()['company_id']]), ['consultor', 'ligacoes', 'minutos', 'custo']) ?>
            </article>
            <?php endif; ?>
            <article class="panel">
                <h2>Gasto por campanha</h2>
                <?= table(rows("SELECT ca.name campanha, COUNT(co.id) ligacoes, ROUND(COALESCE(SUM(co.billable_seconds), 0) / 60.0, 1) minutos, printf('%.2f', COALESCE(SUM({$costMicrosSql}), 0) / 1000000.0) custo FROM campaigns ca LEFT JOIN calls co ON co.campaign_id = ca.id WHERE ca.company_id = ? GROUP BY ca.id ORDER BY COALESCE(SUM({$costMicrosSql}), 0) DESC", [current_user()['company_id']]), ['campanha', 'ligacoes', 'minutos', 'custo']) ?>
            </article>
        </section>
        <?php if (!is_platform_admin()): ?>
        <?php $paymentCheckoutOpen = (string)$billing['state'] !== 'active' || (bool)$selectedPayment; ?>
        <details class="panel payment-checkout-disclosure" id="pagamento" <?= $paymentCheckoutOpen ? 'open' : '' ?>>
            <summary><span><strong>Pagar ou renovar</strong><small><?= $paymentCheckoutOpen ? 'Escolha uma forma de pagamento' : 'Plano vigente. Abra para antecipar a renovacao.' ?></small></span><span class="payment-checkout-chevron" aria-hidden="true"></span></summary>
            <div class="payment-checkout-content">
            <?php if (!$subscription): ?>
                <p class="empty">Nenhum plano esta vinculado a esta conta. Entre em contato com o administrador.</p>
            <?php elseif (!$paymentConfig['active']): ?>
                <p class="empty">Pagamento online ainda nao foi habilitado pelo administrador.</p>
            <?php else: ?>
                <?php
                $paymentMethods = [
                    'card' => ['label' => 'Cartao de credito', 'icon' => '&#9645;', 'enabled' => (bool)$paymentConfig['card_enabled']],
                    'pix' => ['label' => 'Pix', 'icon' => '&#9670;', 'enabled' => (bool)$paymentConfig['pix_enabled']],
                    'boleto' => ['label' => 'Boleto bancario', 'icon' => '&#9638;', 'enabled' => (bool)$paymentConfig['boleto_enabled']],
                ];
                $enabledMethods = array_filter($paymentMethods, static fn(array $method): bool => $method['enabled']);
                $preferredMethod = strtolower((string)($selectedPayment['payment_method'] ?? ($_GET['payment_method'] ?? '')));
                $defaultMethod = isset($enabledMethods[$preferredMethod])
                    ? $preferredMethod
                    : (string)(array_key_first($enabledMethods) ?? '');
                $paymentInProgress = $selectedPayment
                    && in_array((string)$selectedPayment['status'], ['CREATED', 'PENDING', 'IN_PROCESS'], true);
                ?>
                <?php if (!$enabledMethods): ?>
                    <p class="empty">Nenhum metodo de pagamento esta habilitado no momento.</p>
                <?php else: ?>
                    <p class="hint">Escolha como deseja pagar. O valor e o periodo sao definidos pelo plano da conta.</p>
                    <div class="payment-method-selector" role="tablist" aria-label="Forma de pagamento">
                        <?php foreach ($paymentMethods as $methodKey => $method): ?>
                            <?php $methodDisabled = !$method['enabled'] || ($paymentInProgress && $methodKey !== $defaultMethod); ?>
                            <button type="button" class="payment-method-option<?= $methodKey === $defaultMethod ? ' active' : '' ?>" data-payment-method-tab="<?= h($methodKey) ?>" role="tab" aria-selected="<?= $methodKey === $defaultMethod ? 'true' : 'false' ?>" <?= $methodDisabled ? 'disabled' : '' ?>>
                                <span class="payment-method-icon payment-method-icon-<?= h($methodKey) ?>" aria-hidden="true"><?= $method['icon'] ?></span>
                                <strong><?= h($method['label']) ?></strong>
                                <small><?= !$method['enabled'] ? 'Indisponivel' : (($paymentInProgress && $methodKey === $defaultMethod) ? 'Pagamento em andamento' : 'Disponivel') ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($paymentConfig['pix_enabled']): ?>
                        <div class="payment-method-panel" data-payment-method-panel="pix" <?= $defaultMethod !== 'pix' ? 'hidden' : '' ?>>
                            <div class="payment-guide-brand"><span class="payment-guide-logo payment-guide-logo-pix">&#9670;</span><strong>Pix</strong></div>
                            <div class="payment-guide-steps">
                                <div><span>1</span><p>Finalize a compra para gerar o QR Code Pix.</p></div>
                                <div><span>2</span><p>Abra o aplicativo do seu banco e escolha pagar com Pix.</p></div>
                                <div><span>3</span><p>Aponte a camera para o codigo ou use o Pix copia e cola.</p></div>
                            </div>
                            <?php if ($paymentInProgress && $defaultMethod === 'pix'): ?>
                                <p class="payment-progress-note">Pix gerado. Conclua o pagamento usando o QR Code abaixo.</p>
                            <?php else: ?>
                                <form method="post"><input type="hidden" name="action" value="create_payment"><input type="hidden" name="payment_method" value="pix"><button class="button" type="submit">Gerar Pix</button></form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($paymentConfig['card_enabled']): ?>
                        <div class="payment-method-panel" data-payment-method-panel="card" <?= $defaultMethod !== 'card' ? 'hidden' : '' ?>>
                        <div class="payment-guide-brand"><span class="payment-guide-logo">&#9645;</span><strong>Cartao de credito</strong></div>
                        <p class="payment-guide-intro">Informe os dados abaixo. O LigFlow nao armazena o numero completo do cartao nem o codigo de seguranca.</p>
                        <?php if ($paymentInProgress && $defaultMethod === 'card'): ?>
                            <p class="payment-progress-note">Pagamento com cartao em processamento. Aguarde a confirmacao.</p>
                        <?php else: ?>
                        <form id="mp-card-form" method="post" class="form-grid payment-card-form">
                            <input type="hidden" name="action" value="create_payment"><input type="hidden" name="payment_method" value="card">
                            <label class="payment-card-wide">Nome no cartao<input id="form-checkout__cardholderName" placeholder="Nome completo"></label>
                            <label class="payment-card-wide">Numero do cartao<div id="form-checkout__cardNumber" class="mp-field"></div></label>
                            <label>Validade (mes/ano)<div id="form-checkout__expirationDate" class="mp-field"></div></label>
                            <label>CVC<div id="form-checkout__securityCode" class="mp-field"></div></label>
                            <div class="payment-card-technical" aria-hidden="true">
                                <select id="form-checkout__issuer" tabindex="-1"></select><select id="form-checkout__installments" tabindex="-1"></select>
                                <select id="form-checkout__identificationType" tabindex="-1"></select><input id="form-checkout__identificationNumber" tabindex="-1"><input id="form-checkout__cardholderEmail" type="email" value="<?= h($user['email']) ?>" tabindex="-1">
                            </div>
                            <input type="hidden" name="token" id="mp-token"><input type="hidden" name="payment_method_id" id="mp-payment-method"><input type="hidden" name="issuer_id" id="mp-issuer"><input type="hidden" name="installments" id="mp-installments">
                            <button class="button" type="submit" id="form-checkout__submit">Confirmar</button>
                        </form>
                        <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($paymentConfig['boleto_enabled']): ?>
                        <div class="payment-method-panel" data-payment-method-panel="boleto" <?= $defaultMethod !== 'boleto' ? 'hidden' : '' ?>>
                            <div class="payment-guide-brand"><span class="payment-guide-logo">&#9638;</span><strong>Boleto bancario</strong></div>
                            <div class="payment-guide-steps">
                                <div><span>1</span><p>Preencha os dados do pagador.</p></div>
                                <div><span>2</span><p>Gere o boleto e abra o documento.</p></div>
                                <div><span>3</span><p>Pague pelo aplicativo do banco ou em um local autorizado.</p></div>
                            </div>
                            <?php if ($paymentInProgress && $defaultMethod === 'boleto'): ?>
                                <p class="payment-progress-note">Boleto gerado. Use o link abaixo para abrir e pagar.</p>
                            <?php else: ?>
                            <form method="post" class="form-grid payment-boleto-form">
                                <input type="hidden" name="action" value="create_payment"><input type="hidden" name="payment_method" value="boleto">
                                <label>Nome<input name="first_name" required></label><label>Sobrenome<input name="last_name" required></label><label>CPF<input name="cpf" inputmode="numeric" required></label>
                                <button class="button" type="submit">Gerar boleto</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($selectedPayment): ?>
                <?php
                $isPixPayment = (string)$selectedPayment['payment_method'] === 'pix';
                $showPixModal = $isPixPayment && !empty($checkout['qr_code_base64']);
                ?>
                <?php if ($showPixModal): ?>
                    <div class="payment-pix-modal" data-payment-pix-modal data-payment-watch="<?= (int)$selectedPayment['id'] ?>" role="dialog" aria-modal="true" aria-labelledby="payment-pix-title">
                        <div class="payment-pix-dialog">
                            <header><div><strong id="payment-pix-title">Pague com Pix</strong><small>Status: <span data-payment-status><?= h($selectedPayment['status']) ?></span></small></div><button class="icon-button" type="button" data-payment-pix-close aria-label="Fechar">x</button></header>
                            <img class="pix-qr" src="data:image/png;base64,<?= h($checkout['qr_code_base64']) ?>" alt="QR Code Pix">
                            <?php if (!empty($checkout['qr_code'])): ?><textarea readonly><?= h($checkout['qr_code']) ?></textarea><div class="payment-pix-actions"><button class="button secondary" type="button" data-copy-pix>Copiar codigo Pix</button><button class="button" type="button" data-check-payment data-payment-id="<?= (int)$selectedPayment['id'] ?>">Verificar pagamento</button></div><?php endif; ?>
                            <button class="button secondary" type="button" data-payment-pix-close>Fechar</button>
                        </div>
                    </div>
                <?php else: ?>
                    <article class="payment-result" data-payment-watch="<?= (int)$selectedPayment['id'] ?>"><strong>Status: <span data-payment-status><?= h($selectedPayment['status']) ?></span></strong>
                        <?php if (!empty($checkout['qr_code'])): ?><textarea readonly><?= h($checkout['qr_code']) ?></textarea><?php endif; ?>
                        <?php if ($isPixPayment && !empty($checkout['qr_code'])): ?><div class="payment-pix-actions"><button class="button secondary" type="button" data-copy-pix>Copiar codigo Pix</button><button class="button" type="button" data-check-payment data-payment-id="<?= (int)$selectedPayment['id'] ?>">Verificar pagamento</button></div><?php endif; ?>
                        <?php if (!empty($checkout['ticket_url'])): ?><a class="button secondary" href="<?= h($checkout['ticket_url']) ?>" target="_blank" rel="noopener">Abrir boleto</a><?php endif; ?>
                    </article>
                <?php endif; ?>
            <?php endif; ?>
            </div>
        </details>
        <details class="panel import-history-disclosure">
            <summary><span>Historico de pagamentos</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="import-history-content"><?= table($payments, ['created_at','internal_reference','payment_method','amount','billing_period','status','approved_at','expires_at'], $tenantTimezone) ?></div>
        </details>
        <?php if ($paymentConfig['active'] && $paymentConfig['card_enabled'] && $paymentConfig['public_key'] !== ''): ?>
        <script src="https://sdk.mercadopago.com/js/v2"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const disclosure = document.querySelector('.payment-checkout-disclosure');
            let initialized = false;
            const initializeCardForm = function () {
                if (initialized || !window.MercadoPago || !document.getElementById('mp-card-form')) return;
                initialized = true;
                const mp = new MercadoPago(<?= json_encode($paymentConfig['public_key']) ?>, {locale:'pt-BR'});
                const cardForm = mp.cardForm({amount:<?= json_encode(number_format((float)($subscription['monthly_price'] ?? 0),2,'.','')) ?>,iframe:true,form:{id:'mp-card-form',cardNumber:{id:'form-checkout__cardNumber'},expirationDate:{id:'form-checkout__expirationDate'},securityCode:{id:'form-checkout__securityCode'},cardholderName:{id:'form-checkout__cardholderName'},issuer:{id:'form-checkout__issuer'},installments:{id:'form-checkout__installments'},identificationType:{id:'form-checkout__identificationType'},identificationNumber:{id:'form-checkout__identificationNumber'},cardholderEmail:{id:'form-checkout__cardholderEmail'}},callbacks:{onFormMounted:function(error){if(error)console.error(error);},onSubmit:function(event){event.preventDefault();const data=cardForm.getCardFormData();document.getElementById('mp-token').value=data.token;document.getElementById('mp-payment-method').value=data.paymentMethodId;document.getElementById('mp-issuer').value=data.issuerId;document.getElementById('mp-installments').value=data.installments;event.target.submit();}}});
            };
            if (!disclosure || disclosure.open) initializeCardForm();
            disclosure?.addEventListener('toggle', function () { if (disclosure.open) initializeCardForm(); });
        });
        </script>
        <?php endif; ?>
        <?php endif; ?>
    <?php });
}

function render_settings(): void
{
    layout('settings', function () {
        $user = current_user();
        $companyId = (int)$user['company_id'];
        if (is_platform_admin($user) && isset($_GET['company_id'])) {
            $companyId = (int)$_GET['company_id'];
        }
        $companies = rows('SELECT id, trade_name FROM companies ORDER BY trade_name');
        $integrations = rows("SELECT * FROM integration_settings WHERE company_id = ? ORDER BY provider = 'nvoip' DESC, integration_name, provider", [$companyId]);
        $isNew = isset($_GET['new']) || !$integrations;
        $selectedProvider = integration_provider_key((string)($_GET['provider'] ?? ''));
        if (!$isNew && $selectedProvider === '') {
            $selectedProvider = (string)($integrations[0]['provider'] ?? '');
        }
        $selected = $isNew ? null : one('SELECT * FROM integration_settings WHERE company_id = ? AND provider = ?', [$companyId, $selectedProvider]);
        $config = $selected ? array_merge(blank_integration_config(), $selected) : blank_integration_config();
        if (!$selected && !$isNew && $selectedProvider === 'nvoip') {
            $config = array_merge(blank_integration_config(), nvoip_config($companyId), ['provider' => 'nvoip', 'integration_name' => 'Nvoip']);
        }
        if (!empty($config['sip_password']) && (str_starts_with((string)$config['sip_password'], 'enc:') || str_starts_with((string)$config['sip_password'], 'plain:'))) {
            $config['sip_password'] = decrypt_secret((string)$config['sip_password']);
        }
        $webhookUrl = $config['webhook_url'] ?: 'http://localhost/voipCalutec/?page=nvoip_webhook';
        $urlWarning = ($config['provider'] === 'nvoip' || $selectedProvider === 'nvoip') ? nvoip_api_url_error((string)$config['api_url']) : null;
        $webhookIsLocal = preg_match('~https?://(localhost|127\.0\.0\.1)~i', $webhookUrl) === 1;
        $mpConfig = mercado_pago_config();
        $mpStored = one('SELECT * FROM payment_settings WHERE id=1') ?: [];
        $googlePlacesConfig = google_places_config();
        $googlePlacesStored = one('SELECT * FROM google_places_settings WHERE id=1') ?: [];
        $telephonyModeConfig = asterisk_config();
        ?>
        <?php if (is_platform_admin($user)): ?>
        <details class="panel import-history-disclosure" id="google-places">
            <summary><span>Google Places API</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="import-history-content">
                <div class="section-head"><div><h2>Google Places API (New)</h2><p>Configuracao global para o Radar de Leads. A chave nunca e enviada ao navegador.</p></div><span class="status-badge <?= $googlePlacesConfig['active'] ? 'called' : '' ?>"><?= $googlePlacesConfig['active'] ? 'Ativa' : 'Inativa' ?></span></div>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="save_google_places_settings">
                    <label class="check"><input type="checkbox" name="google_places_active" <?= $googlePlacesConfig['active'] ? 'checked' : '' ?>> Integracao ativa</label>
                    <label>Chave da API<input type="password" name="google_places_api_key" placeholder="<?= h(masked_secret((string)($googlePlacesStored['api_key_encrypted'] ?? '')) ?: 'Cole a chave do Google Places') ?>"></label>
                    <p class="hint wide">Restrinja a chave no Google Cloud para Places API (New) e para os domínios/IPs permitidos do LigFlow.</p>
                    <button class="button" type="submit">Salvar Google Places</button>
                </form>
                <form method="post"><input type="hidden" name="action" value="test_google_places"><button class="button secondary" type="submit">Testar conexao</button></form>
            </div>
        </details>
        <details class="panel import-history-disclosure" id="mercado-pago">
            <summary><span>Metodo de pagamento</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="import-history-content">
            <div class="section-head"><div><h2>Metodo de pagamento</h2><p>Configuracao global de cobranca dos planos LigFlow.</p></div><span class="status-badge <?= $mpConfig['active'] ? 'called' : '' ?>"><?= $mpConfig['active'] ? 'Ativa' : 'Inativa' ?></span></div>
            <form method="post" class="form-grid">
                <input type="hidden" name="action" value="save_mercado_pago_settings">
                <label class="check"><input type="checkbox" name="active" <?= $mpConfig['active']?'checked':'' ?>> Integracao ativa</label>
                <label>Ambiente<select name="environment"><option value="test" <?= $mpConfig['environment']==='test'?'selected':'' ?>>Teste</option><option value="production" <?= $mpConfig['environment']==='production'?'selected':'' ?>>Producao</option></select></label>
                <label>Public Key<input name="public_key" value="<?= h($mpConfig['public_key']) ?>"></label>
                <label>Access Token<input type="password" name="access_token" placeholder="<?= h(masked_secret((string)($mpStored['access_token_encrypted'] ?? '')) ?: 'Informe o Access Token') ?>"></label>
                <label>Segredo do webhook<input type="password" name="webhook_secret" placeholder="<?= h(masked_secret((string)($mpStored['webhook_secret_encrypted'] ?? '')) ?: 'Informe o segredo') ?>"></label>
                <label class="check"><input type="checkbox" name="pix_enabled" <?= $mpConfig['pix_enabled']?'checked':'' ?>> Pix ativo</label>
                <label class="check"><input type="checkbox" name="card_enabled" <?= $mpConfig['card_enabled']?'checked':'' ?>> Cartao ativo</label>
                <label class="check"><input type="checkbox" name="boleto_enabled" <?= $mpConfig['boleto_enabled']?'checked':'' ?>> Boleto ativo</label>
                <p class="hint wide">Webhook: <?= h(rtrim(env_value('APP_URL', 'http://localhost/voipCalutec'), '/') . '/?page=mercado_pago_webhook') ?></p>
                <button class="button" type="submit">Salvar Mercado Pago</button>
            </form>
            <form method="post"><input type="hidden" name="action" value="test_mercado_pago"><button class="button secondary" type="submit">Testar conexao</button></form>
            </div>
        </details>
        <?php endif; ?>
        <details class="panel import-history-disclosure" id="integracoes-cadastradas">
            <summary><span>Integracoes cadastradas</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="import-history-content">
                        <?php if (is_platform_admin($user)): ?>
                <div class="section-head">
                    <div>
                        <h2>Telefonia ativa da plataforma</h2>
                        <p>Define o provedor usado nas novas chamadas de todos os usuarios.</p>
                    </div>
                    <form method="post" class="inline">
                        <input type="hidden" name="action" value="save_global_telephony_mode">
                        <label>Provedor ativo
                            <select name="active_mode">
                                <option value="NVOIP_DIRECT" <?= $telephonyModeConfig['active_mode'] === 'NVOIP_DIRECT' ? 'selected' : '' ?>>Nvoip (nativa)</option>
                                <option value="ASTERISK" <?= $telephonyModeConfig['active_mode'] === 'ASTERISK' ? 'selected' : '' ?> <?= empty($telephonyModeConfig['enabled']) ? 'disabled' : '' ?>>Asterisk<?= empty($telephonyModeConfig['enabled']) ? ' (configure primeiro)' : '' ?></option>
                            </select>
                        </label>
                        <button class="button" type="submit">Salvar provedor ativo</button>
                    </form>
                </div>
            <?php endif; ?>
            <div class="section-head">
                <div>
                    <h2>Integrações</h2>
                    <p>Selecione uma integração existente ou crie uma nova com campos livres.</p>
                </div>
                <a class="button" href="?page=settings&new=1<?= is_platform_admin($user) ? '&company_id=' . $companyId : '' ?>">Adicionar nova</a>
            </div>
            <form method="get" class="inline">
                <input type="hidden" name="page" value="settings">
                <?php if (is_platform_admin($user)): ?>
                    <select name="company_id" onchange="this.form.submit()">
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id'] ?>" <?= $companyId === (int)$company['id'] ? 'selected' : '' ?>><?= h($company['trade_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <select name="provider" onchange="this.form.submit()" <?= !$integrations ? 'disabled' : '' ?>>
                    <?php if (!$integrations): ?>
                        <option>Nenhuma integração cadastrada</option>
                    <?php else: ?>
                        <?php foreach ($integrations as $integration): ?>
                            <option value="<?= h($integration['provider']) ?>" <?= !$isNew && $selectedProvider === $integration['provider'] ? 'selected' : '' ?>>
                                <?= h(($integration['integration_name'] ?: strtoupper($integration['provider'])) . ' (' . $integration['provider'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>
            </div>
        </details>
        <section>
            <details class="panel import-history-disclosure" <?= isset($_GET['new']) || isset($_GET['provider']) ? 'open' : '' ?>>
                <summary><span><?= $isNew ? 'Nova integracao' : 'Integracao Nvoip nativa' ?></span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <div class="grid two import-history-content">
            <form class="form-grid" method="post">
                <input type="hidden" name="action" value="save_integration_settings">
                <input type="hidden" name="company_id" value="<?= (int)$companyId ?>">
                <h2><?= $isNew ? 'Nova integração' : 'Editar integração' ?></h2>
                <label>Nome da integração<input name="integration_name" value="<?= h((string)$config['integration_name']) ?>" placeholder="Ex: Nvoip, Twilio, Plivo"></label>
                <label>Identificador interno<input name="provider" value="<?= h((string)$config['provider']) ?>" placeholder="Ex: nvoip, twilio, plivo"></label>
                <label>Modo
                    <select name="mode">
                        <option value="simulate" <?= $config['mode'] === 'simulate' ? 'selected' : '' ?>>Demonstração</option>
                        <option value="api" <?= $config['mode'] === 'api' ? 'selected' : '' ?>>API real</option>
                    </select>
                </label>
                <label>URL da API<input name="api_url" value="<?= h((string)$config['api_url']) ?>" placeholder="https://..."></label>
                <label>Autenticação
                    <select name="auth_method">
                        <option value="napikey" <?= ($config['auth_method'] ?? 'napikey') === 'napikey' ? 'selected' : '' ?>>Napikey na URL</option>
                        <option value="oauth" <?= ($config['auth_method'] ?? 'napikey') === 'oauth' ? 'selected' : '' ?>>OAuth NumberSIP + User Token</option>
                        <option value="bearer" <?= ($config['auth_method'] ?? 'napikey') === 'bearer' ? 'selected' : '' ?>>Bearer Token direto</option>
                        <option value="custom" <?= ($config['auth_method'] ?? 'napikey') === 'custom' ? 'selected' : '' ?>>Personalizada</option>
                    </select>
                </label>
                <label>Napikey<input name="napikey" type="password" placeholder="<?= h(masked_secret((string)$config['napikey']) ?: 'Cole a Napikey') ?>"></label>
                <label>NumberSIP<input name="numbersip" type="password" placeholder="<?= h(masked_secret((string)$config['numbersip']) ?: 'Cole o NumberSIP') ?>"></label>
                <label>Usuário/Ramal SIP<input name="user_sip" type="password" placeholder="<?= h(masked_secret((string)$config['user_sip']) ?: 'Ex: ramal/usuário SIP que origina') ?>"></label>
                <label>WSS SIP/WebRTC<input name="sip_wss_url" value="<?= h((string)$config['sip_wss_url']) ?>" placeholder="wss://app.nvoip.com.br:7443"></label>
                <label>Dominio SIP<input name="sip_domain" value="<?= h((string)$config['sip_domain']) ?>" placeholder="app.nvoip.com.br"></label>
                <label>Senha SIP<input name="sip_password" type="password" placeholder="<?= h(masked_secret((string)$config['sip_password']) ?: 'Senha SIP da Nvoip') ?>"></label>
                <label>User Token / Bearer<input name="user_token" type="password" placeholder="<?= h(masked_secret((string)$config['user_token']) ?: 'Cole o token') ?>"></label>
                <label>Número de origem<input name="origin_number" value="<?= h((string)$config['origin_number']) ?>" placeholder="+554130000000"></label>
                <label>Custo por minuto<input name="price_per_minute" value="<?= h((string)$config['price_per_minute']) ?>" placeholder="0.06"></label>
                <label>Espera de chamada SIP (s)<input name="sip_callback_timeout_seconds" type="number" min="10" value="<?= h((string)$config['sip_callback_timeout_seconds']) ?>"></label>
                <label class="check"><input type="checkbox" name="auto_answer_nvoip_callback" <?= (int)$config['auto_answer_nvoip_callback'] === 1 ? 'checked' : '' ?>> Atender chamadas SIP automaticamente</label>
                <label class="check"><input type="checkbox" name="recording_enabled" <?= (int)$config['recording_enabled'] === 1 ? 'checked' : '' ?>> Gravar ligações</label>
                <label class="wide">Payload JSON opcional<textarea name="payload_template" rows="7" placeholder='{"caller":"{{user_sip}}","called":"{{destination}}","bina":"{{origin}}"}'><?= h((string)$config['payload_template']) ?></textarea></label>
                <label class="wide">URL de webhook<input name="webhook_url" value="<?= h($webhookUrl) ?>"></label>
                <label class="wide">Segredo do webhook<input name="webhook_secret" value="<?= h($config['webhook_secret']) ?>" placeholder="chave para validar callbacks"></label>
                <label class="wide">Campos adicionais JSON<textarea name="extra_config" rows="5" placeholder='{"campo_especifico":"valor"}'><?= h((string)$config['extra_config']) ?></textarea></label>
                <button class="button">Salvar integração</button>
                <p class="hint">Todos os campos são opcionais, exceto nome ou identificador. Campos de senha vazios mantêm o valor salvo ao editar.</p>
            </form>
            <article class="form-grid">
                <h2>Status da integração</h2>
                <dl>
                    <dt>Integração</dt><dd><?= h(($config['integration_name'] ?: strtoupper((string)$config['provider'])) ?: 'Nova') ?></dd>
                    <dt>Identificador</dt><dd><?= h((string)($config['provider'] ?: 'Não definido')) ?></dd>
                    <dt>Modo atual</dt><dd><?= h($config['mode'] === 'api' ? 'API real' : 'Demonstração') ?></dd>
                    <dt>Status</dt><dd><?= h($config['mode'] === 'api' && $config['api_url'] ? 'Configurada para API' : 'Sem API real ativa') ?></dd>
                    <dt>URL da API</dt><dd><?= h($config['api_url'] ?: 'Não configurada') ?></dd>
                    <dt>Autenticação</dt><dd><?= h(($config['auth_method'] ?? 'napikey') === 'oauth' ? 'OAuth' : 'Napikey') ?></dd>
                    <dt>Napikey</dt><dd><?= h(masked_secret((string)$config['napikey']) ?: 'Não configurada') ?></dd>
                    <dt>NumberSIP</dt><dd><?= h(masked_secret((string)$config['numbersip']) ?: 'Não configurado') ?></dd>
                    <dt>Usuário SIP</dt><dd><?= h(masked_secret((string)$config['user_sip']) ?: 'Não configurado') ?></dd>
                    <dt>WSS SIP</dt><dd><?= h($config['sip_wss_url'] ?: 'Não configurado') ?></dd>
                    <dt>Domínio SIP</dt><dd><?= h($config['sip_domain'] ?: 'Não configurado') ?></dd>
                    <dt>Senha SIP</dt><dd><?= h(masked_secret((string)$config['sip_password']) ?: 'Não configurada') ?></dd>
                    <dt>Autoatendimento</dt><dd><?= (int)$config['auto_answer_nvoip_callback'] === 1 ? 'Ativo' : 'Inativo' ?></dd>
                    <dt>User Token</dt><dd><?= h(masked_secret((string)$config['user_token']) ?: 'Nao configurado') ?></dd>
                    <dt>Origem</dt><dd><?= h($config['origin_number'] ?: 'Não configurada') ?></dd>
                    <dt>Webhook</dt><dd><?= h($webhookUrl) ?></dd>
                </dl>
                
                <?php if ($urlWarning && $config['mode'] === 'api' && ($config['provider'] === 'nvoip' || $selectedProvider === 'nvoip')): ?>
                    <div class="flash error"><?= h($urlWarning) ?></div>
                <?php endif; ?>
                
                
            </article>
            </div>
            <?php if ($webhookIsLocal): ?>
                    <div class="flash error">Este webhook esta em localhost. Para a Nvoip enviar status e gravacoes, configure uma URL publica HTTPS do LigFlow.</div>
                <?php endif; ?>
            </details>
        </section>
        <?php render_asterisk_settings_section(); ?>
        <?php render_sip_diagnostic_sections(); ?>
    <?php });
}

function render_asterisk_settings_section(): void
{
    $user = current_user();
    if (!is_platform_admin($user)) return;
    $saved = one('SELECT * FROM asterisk_settings WHERE id = 1') ?: [];
    $config = asterisk_config();
    $hasPassword = !empty($saved['ari_password_encrypted']);
    $hasWebrtcPassword = !empty($saved['webrtc_password_encrypted']);
    $webrtcPasswordSource = $hasWebrtcPassword ? 'Painel' : (trim((string)env_value('ASTERISK_WEBRTC_PASSWORD')) !== '' ? 'Variavel de ambiente' : 'Ausente');
    $webrtcReady = valid_asterisk_webrtc_wss_url((string)$config['sip_wss_url'])
        && valid_asterisk_webrtc_domain((string)$config['sip_domain'])
        && valid_asterisk_webrtc_endpoint((string)$config['consultant_endpoint'])
        && valid_asterisk_trunk_identifier((string)$config['webrtc_context'])
        && $webrtcPasswordSource !== 'Ausente';
    ?>
    <section>
        <details class="panel import-history-disclosure" id="asterisk">
            <summary><span>Asterisk (ARI)</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <form class="form-grid import-history-content" method="post">
                <input type="hidden" name="action" value="save_asterisk_settings">
                <h2>Telefonia via Asterisk</h2>
                <p class="hint wide">Apenas novas chamadas usam o modo selecionado. Chamadas iniciadas preservam permanentemente sua rota e tronco.</p>
                <label class="check"><input type="checkbox" name="enabled" value="1" <?= !empty($config['enabled']) ? 'checked' : '' ?>> Asterisk habilitado</label>
                <label>Ambiente<select name="environment"><option value="test" <?= $config['environment'] === 'test' ? 'selected' : '' ?>>Teste</option><option value="production" <?= $config['environment'] === 'production' ? 'selected' : '' ?>>Producao</option></select></label>
                <label>Modo ativo para novas chamadas<select name="active_mode"><option value="NVOIP_DIRECT" <?= $config['active_mode'] === 'NVOIP_DIRECT' ? 'selected' : '' ?>>NVOIP_DIRECT</option><option value="ASTERISK" <?= $config['active_mode'] === 'ASTERISK' ? 'selected' : '' ?>>ASTERISK</option></select></label>
                <label>Rota ativa no Asterisk<select name="active_route"><option value="NVOIP_TRUNK" <?= $config['active_route'] === 'NVOIP_TRUNK' ? 'selected' : '' ?>>Nvoip (NVOIP_TRUNK)</option><option value="DIRECTCALL_TRUNK" <?= $config['active_route'] === 'DIRECTCALL_TRUNK' ? 'selected' : '' ?>>DirectCall (DIRECTCALL_TRUNK)</option></select></label>
                <label>URL do ARI<input name="ari_url" type="url" value="<?= h($config['ari_url']) ?>" placeholder="https://asterisk.exemplo/ari"></label>
                <label>URL WebSocket ARI<input name="ari_ws_url" type="url" value="<?= h($config['ari_ws_url']) ?>" placeholder="wss://asterisk.exemplo/ari/events"></label>
                <label>Usuario ARI<input name="ari_username" value="<?= h($config['ari_username']) ?>"></label>
                <label>Senha ARI<input name="ari_password" type="password" placeholder="<?= $hasPassword ? 'Senha salva (deixe vazio para manter)' : 'Senha ARI' ?>"></label>
                <label>Aplicacao Stasis<input name="stasis_app" value="<?= h($config['stasis_app']) ?>"></label>
                <label>Ramal/endpoint do consultor<input name="consultant_endpoint" value="<?= h($config['consultant_endpoint']) ?>" placeholder="PJSIP/1001"></label>
                <label>Senha SIP/WebRTC<input name="webrtc_password" type="password" autocomplete="new-password" placeholder="<?= $hasWebrtcPassword ? 'Senha salva (deixe vazio para manter)' : 'Senha SIP/WebRTC' ?>"></label>
                <label>Contexto WebRTC<input name="webrtc_context" value="<?= h($config['webrtc_context']) ?>" pattern="[A-Za-z0-9_-]+" placeholder="from-ligflow-webrtc"></label>
                <p class="hint wide">A senha ARI e a senha SIP/WebRTC sao independentes.</p>
                <label>Timeout de originacao (segundos)<input name="originate_timeout_seconds" type="number" min="5" value="<?= (int)$config['originate_timeout_seconds'] ?>"></label>
                <label>Timeout de bridge (segundos)<input name="bridge_timeout_seconds" type="number" min="5" value="<?= (int)$config['bridge_timeout_seconds'] ?>"></label>
                <label>Reconexao inicial (segundos)<input name="reconnect_initial_seconds" type="number" min="1" value="<?= (int)$config['reconnect_initial_seconds'] ?>"></label>
                <label>Reconexao maxima (segundos)<input name="reconnect_max_seconds" type="number" min="2" value="<?= (int)$config['reconnect_max_seconds'] ?>"></label>
                <label>WSS SIP/WebRTC<input name="sip_wss_url" type="url" value="<?= h($config['sip_wss_url']) ?>"></label>
                <label>Dominio SIP/WebRTC<input name="sip_domain" value="<?= h($config['sip_domain']) ?>"></label>
                <label>Tronco Nvoip<input name="nvoip_trunk" value="<?= h($config['nvoip_trunk']) ?>" readonly></label>
                <label>Tronco DirectCall<input name="directcall_trunk" value="<?= h($config['directcall_trunk']) ?>" pattern="[A-Za-z0-9_-]+"></label>
                <label>Inicio da faixa de ramais<input name="extension_start" type="number" min="1" value="<?= (int)$config['extension_start'] ?>"></label>
                <label>Fim da faixa de ramais<input name="extension_end" type="number" min="1" value="<?= (int)$config['extension_end'] ?>"></label>
                <label>URL do agente de provisionamento<input name="provisioning_agent_url" type="url" value="<?= h($config['provisioning_agent_url']) ?>" placeholder="https://agente.exemplo/asterisk"></label>
                <label>Segredo do agente<input name="provisioning_agent_secret" type="password" placeholder="<?= !empty($saved['provisioning_agent_secret_encrypted']) ? 'Segredo salvo (deixe vazio para manter)' : 'Segredo HMAC' ?>"></label>
                <label>Timeout do agente (segundos)<input name="provisioning_agent_timeout_seconds" type="number" min="3" max="60" value="<?= (int)$config['provisioning_agent_timeout_seconds'] ?>"></label>
                <?php if (strcasecmp($config['directcall_trunk'], 'DIRECTCALL_TRUNK') === 0): ?><p class="hint wide">O tronco salvo parece ser o nome logico anterior. Confira o endpoint PJSIP real no Asterisk antes de salvar.</p><?php endif; ?>
                <label class="wide">Configuracao operacional Nvoip (JSON)<textarea name="nvoip_trunk_config_json" rows="2"><?= h((string)($saved['nvoip_trunk_config_json'] ?? '{}')) ?></textarea></label>
                <label class="wide">Configuracao operacional DirectCall (JSON)<textarea name="directcall_trunk_config_json" rows="2"><?= h((string)($saved['directcall_trunk_config_json'] ?? '{}')) ?></textarea></label>
                <div class="script-box wide"><strong>Saude configurada</strong><p>Servidor/ARI: <?= $config['ari_url'] && $config['ari_username'] && $hasPassword ? 'configurado' : 'pendente' ?>; WebSocket: <?= $config['ari_ws_url'] ? 'configurado' : 'pendente' ?>; WebRTC: <?= $webrtcReady ? 'configurado' : 'pendente' ?>; senha WebRTC: <?= h($webrtcPasswordSource) ?>. Use Testar conexao para validar o ARI.</p></div>
                <div class="button-row wide"><button class="button">Salvar Asterisk</button><button class="button secondary" name="action" value="test_asterisk_connection">Testar conexao</button></div>
            </form>
        </details>
    </section>
    <?php
}
function render_sip_diagnostic_sections(): void
{
    $config = nvoip_config((int)current_user()['company_id']);
    ?>
        <section>
            <details class="panel import-history-disclosure" id="diagnostico-sip" <?= isset($_GET['sip']) ? 'open' : '' ?>>
                <summary><span>Diagnostico SIP/WebRTC e status do webphone</span><span class="import-history-chevron" aria-hidden="true"></span></summary>
            <form class="form-grid import-history-content" method="post" data-sip-diagnostic>
                <input type="hidden" name="action" value="save_sip_diagnostic_config">
                <h2>Diagnostico SIP/WebRTC Nvoip</h2>
                <p class="hint wide">Use esta tela para provar o registro SIP no navegador. O discador e o webfone flutuante usam este mesmo caminho de chamada.</p>
                <label>WSS URL<input name="sip_wss_url" data-sip-wss value="<?= h((string)$config['sip_wss_url']) ?>"></label>
                <label>Dominio SIP<input name="sip_domain" data-sip-domain value="<?= h((string)$config['sip_domain']) ?>"></label>
                <label>Usuario SIP<input name="user_sip" data-sip-username value="<?= h((string)($config['user_sip'] ?: $config['numbersip'])) ?>"></label>
                <label>Senha SIP<input name="sip_password" data-sip-password type="password" placeholder="<?= h($config['sip_password'] ? 'Senha salva disponivel' : 'Senha SIP da Nvoip') ?>"></label>
                <label class="wide">Numero para teste direto<input data-sip-destination data-phone-mask inputmode="tel" placeholder="Ex: (41) 99631-0725"></label>
                <label class="check"><input type="checkbox" data-sip-use-saved checked> Usar configuracao salva da integracao</label>
                <label class="check"><input type="checkbox" name="auto_answer_nvoip_callback" data-sip-auto-answer <?= (int)$config['auto_answer_nvoip_callback'] === 1 ? 'checked' : '' ?>> Autoatender chamadas SIP recebidas</label>
                <div class="button-row wide">
                    <button class="button" type="button" data-sip-connect>Conectar e registrar</button>
                    <button class="button secondary" type="button" data-sip-place-call>Ligar teste SIP</button>
                    <button class="button secondary" type="button" data-sip-disconnect>Desconectar</button>
                    <button class="button secondary" type="button" data-sip-mic>Testar microfone</button>
                    <button class="button secondary" type="submit">Salvar senha SIP</button>
                </div>
                <div class="button-row wide">
                    <button class="button" type="button" data-sip-answer>Atender</button>
                    <button class="button secondary" type="button" data-sip-reject>Rejeitar</button>
                    <button class="button danger" type="button" data-sip-hangup>Desligar</button>
                    <button class="button secondary" type="button" data-sip-mute>Silenciar</button>
                </div>
                <audio id="nvoip-remote-audio" autoplay></audio>
            </form>
            <article class="import-history-content">
                <h2>Status do webphone</h2>
                <dl class="sip-status-grid">
                    <dt>Estado</dt><dd data-sip-status>DISCONNECTED</dd>
                    <dt>WebSocket</dt><dd data-sip-ws>Desconectado</dd>
                    <dt>Registro</dt><dd data-sip-register>Sem registro</dd>
                    <dt>Chamada</dt><dd data-sip-call>Nenhuma</dd>
                    <dt>Audio</dt><dd data-sip-audio>Aguardando</dd>
                </dl>
                <div class="script-box">
                    <strong>Fluxo esperado</strong>
                    <p>Primeiro teste a chamada direta SIP nesta tela. Depois use o Discador ou o webfone flutuante, que usam o mesmo registro SIP do navegador.</p>
                </div>
                <h3>Eventos sanitizados</h3>
                <ol class="sip-log" data-sip-log></ol>
            </article>
            </details>
        </section>
    <?php
}

function render_sip_diagnostic(): void
{
    redirect('?page=settings&sip=1#diagnostico-sip');
}

function render_blocklist(): void
{
    layout('blocklist', function () {
        [$clause, $params] = tenant_clause('b');
        $search = trim((string)($_GET['q'] ?? ''));
        $blockedSql = "SELECT b.phone_e164, b.reason, b.source, b.notes, b.created_at,
                COALESCE((SELECT c.name FROM contacts c WHERE c.company_id = b.company_id AND c.phone_e164 = b.phone_e164 AND TRIM(c.name) <> '' ORDER BY c.id DESC LIMIT 1), '') contact_name
            FROM blocklist b WHERE {$clause}";
        if ($search !== '') {
            $blockedSql .= " AND (EXISTS (SELECT 1 FROM contacts c WHERE c.company_id = b.company_id AND c.phone_e164 = b.phone_e164 AND c.name LIKE ?)";
            $params[] = '%' . $search . '%';
            $phoneSearch = preg_replace('/\D+/', '', $search);
            if ($phoneSearch !== '') {
                $blockedSql .= " OR REPLACE(b.phone_e164, '+', '') LIKE ?";
                $params[] = '%' . $phoneSearch . '%';
            }
            $blockedSql .= ')';
        }
        $blockedRows = rows($blockedSql . ' ORDER BY b.id DESC', $params);
        ?>
        <section class="grid two">
            <form class="panel form-grid" method="post">
                <input type="hidden" name="action" value="add_block">
                <h2>Adicionar bloqueio</h2>
                <label>Telefone<input name="phone" required></label>
                <label>Motivo<select name="reason"><option>Solicitacao do titular</option><option>Numero invalido</option><option>Bloqueio administrativo</option><option>Falecimento</option><option>Restricao comercial</option></select></label>
                <label>Origem<input name="source" value="manual"></label>
                <label>Observacao<textarea name="notes"></textarea></label>
                <button class="button">Bloquear numero</button>
            </form>
            <article class="panel">
                <h2>Numeros bloqueados</h2>
                <form method="get" class="blocklist-search">
                    <input type="hidden" name="page" value="blocklist">
                    <label>Buscar contato ou telefone<input name="q" value="<?= h($search) ?>" placeholder="Nome ou numero"></label>
                    <button class="button" type="submit">Buscar</button>
                    <?php if ($search !== ''): ?><a class="button secondary" href="?page=blocklist">Limpar</a><?php endif; ?>
                </form>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Contato</th><th>Telefone</th><th>Motivo</th><th>Origem</th><th>Observacao</th><th>Criado em</th><th>Acoes</th></tr></thead>
                        <tbody>
                        <?php foreach ($blockedRows as $row): ?>
                            <tr>
                                <td><?= h($row['contact_name'] ?: '-') ?></td>
                                <td><?= h($row['phone_e164']) ?></td>
                                <td><?= h($row['reason']) ?></td>
                                <td><?= h($row['source']) ?></td>
                                <td><?= h($row['notes']) ?></td>
                                <td><?= h(datetime_utc_display((string)$row['created_at'])) ?></td>
                                <td class="actions">
                                    <form method="post" onsubmit="return confirm('Remover este numero do bloqueio?');">
                                        <input type="hidden" name="action" value="delete_blocklist">
                                        <input type="hidden" name="phone" value="<?= h($row['phone_e164']) ?>">
                                        <button class="mini-link danger-link" type="submit">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$blockedRows): ?><tr><td colspan="7" class="empty">Nenhum numero bloqueado encontrado.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    <?php });
}

function render_account(): void
{
    layout('account', function () {
        $user = current_user();
        $company = one('SELECT c.trade_name, p.name plan_name
            FROM companies c
            LEFT JOIN subscriptions s ON s.company_id=c.id
            LEFT JOIN plans p ON p.id=s.plan_id
            WHERE c.id=?
            ORDER BY s.id DESC
            LIMIT 1', [(int)$user['company_id']]);
        $asteriskExtension = asterisk_user_extension_record((int)$user['company_id'], (int)$user['id']);
        $accountExtension = trim((string)($asteriskExtension['extension'] ?? ''));
        ?>
        <section class="grid two">
            <article class="panel profile-card">
                <?= avatar_markup($user, 'avatar large') ?>
                <h2><?= h($user['name']) ?></h2>
                <p><?= h(role_label($user['role'])) ?></p>
                <dl>
                    <dt>E-mail</dt><dd><?= h($user['email']) ?></dd>
                    <dt>Telefone</dt><dd><?= h($user['phone'] ?: '-') ?></dd>
                    <dt>Status</dt><dd><?= h($user['status']) ?></dd>
                    <dt>Conta</dt><dd><?= h($company['trade_name'] ?? '-') ?></dd>
                    <dt>Plano</dt><dd><?= h($company['plan_name'] ?? '-') ?></dd>
                </dl>
                <button class="mini-link terms-review-button" type="button" data-open-terms>Termos de uso e privacidade</button>
            </article>
            <form class="panel form-grid" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_my_account">
                <h2>Editar minha conta</h2>
                <label>Nome<input name="name" value="<?= h($user['name']) ?>" required></label>
                <label>E-mail<input name="email" type="email" value="<?= h($user['email']) ?>" required></label>
                <label>Telefone<input name="phone" value="<?= h($user['phone']) ?>" data-phone-mask inputmode="tel" placeholder="Ex: (41) 99631-0725"></label>
                <label>Ramal<input value="<?= h($accountExtension ?: 'Sem ramal') ?>" disabled></label>
                <label class="account-password-control">
                    <span>Senha</span>
                    <button class="button secondary" type="button" data-password-enable>Alterar senha</button>
                    <span class="password-input-wrap is-hidden" data-password-editor>
                        <input name="current_password" type="password" value="" autocomplete="current-password" placeholder="Senha atual" disabled data-password-input>
                        <input name="new_password" type="password" value="" autocomplete="new-password" placeholder="Nova senha" disabled data-password-input>
                        <input name="confirm_password" type="password" value="" autocomplete="new-password" placeholder="Confirmar nova senha" disabled data-password-input>
                        <button class="password-visibility-button" type="button" data-password-visibility aria-label="Mostrar senhas" title="Mostrar senhas" aria-pressed="false">&#128065;</button>
                    </span>
                </label>
                <label class="wide">Avatar<input name="avatar" type="file" accept="image/png,image/jpeg,image/webp"></label>
                <?php if (!empty($user['avatar_path'])): ?>
                    <label class="check wide"><input type="checkbox" name="remove_avatar"> Remover avatar atual</label>
                <?php endif; ?>
                <p class="hint">Use uma imagem JPG, PNG ou WebP com ate 2 MB. Se nao enviar imagem, o avatar usa suas iniciais.</p>
                <button class="button">Salvar minha conta</button>
            </form>
            <details class="panel password-reset-panel">
                <summary>Nao sei minha senha atual</summary>
                <form class="form-grid compact-form" method="post">
                    <input type="hidden" name="action" value="request_my_password_reset">
                    <p class="hint wide">Digite uma nova senha. Enviaremos um e-mail para confirmar a alteracao antes de atualizar sua conta.</p>
                    <label>Nova senha<input name="reset_password" type="password" autocomplete="new-password" required minlength="6"></label>
                    <label>Confirmar nova senha<input name="reset_password_confirm" type="password" autocomplete="new-password" required minlength="6"></label>
                    <button class="button secondary">Enviar confirmacao por e-mail</button>
                </form>
            </details>
        </section>
    <?php });
}

function render_asterisk_diagnostics(): void
{
    layout('asterisk_diagnostics', function () {
        $user = current_user();
        if (!asterisk_diagnostics_can_access($user)) {
            flash('Voce nao tem permissao para acessar esta area.', 'error');
            redirect('?page=dashboard');
        }
        $companyId = asterisk_diagnostics_company_id($user);
        $filters = asterisk_diagnostics_filters();
        $data = asterisk_diagnostics_payload($companyId, $filters);
        $campaigns = rows('SELECT id, name FROM campaigns WHERE company_id = ? ORDER BY name', [$companyId]);
        $agents = rows('SELECT id, name FROM users WHERE company_id = ? ORDER BY name', [$companyId]);
        $detail = $filters['batch_id'] > 0 ? asterisk_diagnostics_batch_calls($companyId, $filters['batch_id'], max(1, (int)($_GET['call_page'] ?? 1))) : null;
        ?>
        <section class="panel" data-asterisk-diagnostics>
            <div class="section-heading">
                <div><h2>Diagnostico Asterisk</h2><p class="hint">Monitoramento somente leitura dos lotes Asterisk. Atualizacao adaptativa a cada 5 segundos.</p></div>
                <span class="status-badge" data-asterisk-refresh-status>Atualizando</span>
            </div>
            <div class="stats-grid" data-asterisk-health>
                <article class="stat-card"><span>ARI</span><strong data-health-ari><?= h($data['health']['ari']['state']) ?></strong><small><?= h($data['health']['ari']['configured'] ? 'Configurado' : 'Configuracao incompleta') ?></small></article>
                <article class="stat-card"><span>Worker</span><strong data-health-worker><?= h($data['health']['worker']['state']) ?></strong><small data-health-worker-at><?= h($data['health']['worker']['last_event_at'] ? datetime_utc_display($data['health']['worker']['last_event_at']) : '-') ?></small></article>
                <article class="stat-card"><span>WebRTC do consultor</span><strong data-health-webrtc><?= h($data['health']['webrtc']['state']) ?></strong><small data-health-endpoint><?= h($data['health']['webrtc']['endpoint'] ?: '-') ?></small></article>
            </div>
        </section>

        <form class="panel form-grid" method="get">
            <input type="hidden" name="page" value="asterisk_diagnostics">
            <h2>Filtros</h2>
            <label>Campanha<select name="campaign_id"><option value="">Todas</option><?php foreach ($campaigns as $campaign): ?><option value="<?= (int)$campaign['id'] ?>" <?= $filters['campaign_id'] === (int)$campaign['id'] ? 'selected' : '' ?>><?= h($campaign['name']) ?></option><?php endforeach; ?></select></label>
            <label>Consultor<select name="agent_id"><option value="">Todos</option><?php foreach ($agents as $agent): ?><option value="<?= (int)$agent['id'] ?>" <?= $filters['agent_id'] === (int)$agent['id'] ? 'selected' : '' ?>><?= h($agent['name']) ?></option><?php endforeach; ?></select></label>
            <label>Status<select name="status"><option value="">Todos</option><?php foreach (['ORIGINATING','RINGING','WINNER','CONNECTED','NO_WINNER','CANCELLED'] as $status): ?><option value="<?= h($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select></label>
            <label>Batch ID<input name="batch_id" inputmode="numeric" value="<?= $filters['batch_id'] ?: '' ?>"></label>
            <label>De<input type="date" name="from" value="<?= h($filters['from']) ?>"></label>
            <label>Ate<input type="date" name="to" value="<?= h($filters['to']) ?>"></label>
            <button class="button secondary" type="submit">Aplicar filtros</button>
        </form>

        <section class="panel" data-asterisk-alerts>
            <h2>Alertas operacionais</h2>
            <div data-asterisk-alert-list>
                <?php if (!$data['alerts']): ?><p class="hint">Nenhum alerta para os lotes exibidos.</p><?php endif; ?>
                <?php foreach ($data['alerts'] as $alert): ?><p class="alert <?= h($alert['level'] === 'error' ? 'error' : 'warning') ?>"><?= h(($alert['batch_id'] ?? null) ? 'Batch #' . (int)$alert['batch_id'] . ': ' : '') ?><?= h($alert['message']) ?></p><?php endforeach; ?>
            </div>
        </section>

        <section class="panel">
            <div class="section-heading"><div><h2>Lotes Asterisk</h2><p class="hint">Lotes ativos e recentes do tenant selecionado.</p></div><span data-asterisk-total><?= (int)$data['pagination']['total'] ?> lote(s)</span></div>
            <div class="table-wrap"><table><thead><tr><th>Batch</th><th>Tenant</th><th>Campanha</th><th>Consultor</th><th>Status</th><th>Paralelismo</th><th>Rota</th><th>Inicio</th><th>Duracao</th><th>Orig./Ativ./Fim.</th><th>WIN/LOS/LATE</th><th>Continuacao</th></tr></thead><tbody data-asterisk-batches>
                <?php foreach ($data['batches'] as $batch): ?>
                    <?php $batchCreatedAt = utc_storage_timestamp((string)$batch['created_at']); ?>
                    <tr><td><a href="?page=asterisk_diagnostics&amp;batch_id=<?= (int)$batch['id'] ?>">#<?= (int)$batch['id'] ?></a></td><td><?= h($batch['tenant_name']) ?></td><td><?= h($batch['campaign_name'] ?: '-') ?></td><td><?= h($batch['agent_name'] ?: '-') ?></td><td><?= h($batch['status']) ?></td><td><?= (int)$batch['requested_parallelism'] ?> / <?= (int)$batch['effective_parallelism'] ?></td><td><?= h(($batch['telephony_mode'] ?: '-') . ' / ' . ($batch['telephony_trunk'] ?: '-')) ?></td><td><?= h(datetime_utc_display((string)$batch['created_at'])) ?></td><td><?= h(asterisk_diagnostics_duration($batchCreatedAt !== false ? max(0, time() - $batchCreatedAt) : 0)) ?></td><td><?= (int)$batch['originated_count'] ?> / <?= (int)$batch['active_count'] ?> / <?= (int)$batch['finalized_count'] ?></td><td><?= (int)$batch['winner_count'] ?> / <?= (int)$batch['loser_count'] ?> / <?= (int)$batch['late_answered_count'] ?></td><td><?= !empty($batch['next_started_at']) ? h(datetime_utc_display((string)$batch['next_started_at'])) : '-' ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$data['batches']): ?><tr><td colspan="12" class="empty">Nenhum lote encontrado.</td></tr><?php endif; ?>
            </tbody></table></div>
            <?php if ($data['pagination']['pages'] > 1): ?><p class="pagination">Pagina <?= (int)$data['pagination']['page'] ?> de <?= (int)$data['pagination']['pages'] ?></p><?php endif; ?>
        </section>

        <?php if ($detail !== null): ?>
        <section class="panel">
            <div class="section-heading"><div><h2>Chamadas do lote #<?= (int)$filters['batch_id'] ?></h2><p class="hint">Telefones mascarados e dados tecnicos disponiveis apenas para suporte.</p></div><a class="button secondary" href="?page=asterisk_diagnostics">Fechar detalhes</a></div>
            <div class="table-wrap"><table><thead><tr><th>Lead</th><th>Telefone</th><th>Ramal / consultor</th><th>Channel ID</th><th>Status tecnico</th><th>Race outcome</th><th>Inicio / toque / atendida</th><th>Encerramento</th><th>Hangup</th><th>Duracao</th></tr></thead><tbody>
                <?php foreach ($detail['calls'] as $call): ?><tr><td><?= h($call['lead_name'] ?: '-') ?></td><td><?= h($call['phone_masked']) ?></td><td><?= h(($call['asterisk_extension'] ?: '-') . ' / ' . ($call['resolved_agent_name'] ?: '-')) ?></td><td><?= h($call['provider_channel_id'] ?: '-') ?></td><td><?= h($call['status'] . ' / ' . ($call['internal_status'] ?: '-')) ?></td><td><?= h($call['race_outcome'] ?: '-') ?></td><td><?= h(datetime_utc_display((string)$call['started_at'])) ?> / <?= h(datetime_utc_display((string)$call['ringing_at'])) ?> / <?= h(datetime_utc_display((string)$call['answered_at'])) ?></td><td><?= h(datetime_utc_display((string)($call['ended_at'] ?: $call['finalized_at']))) ?></td><td><?= $call['hangup_requested'] ? 'Solicitado' : '-' ?><?= $call['hangup_confirmed'] ? ' / Confirmado' : '' ?></td><td><?= h(asterisk_diagnostics_duration((int)($call['duration_seconds'] ?: $call['billable_seconds'] ?: 0))) ?></td></tr><?php endforeach; ?>
                <?php if (!$detail['calls']): ?><tr><td colspan="10" class="empty">Nenhuma chamada encontrada.</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>
        <?php endif; ?>
        <?php
    });
}
function render_audit(): void
{
    layout('audit', function () {
        [$clause, $params] = tenant_clause('a');
        $auditLogs = rows("SELECT a.created_at, COALESCE(u.name, 'Sistema') usuario, a.action, a.resource, a.ip_address FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id WHERE {$clause} ORDER BY a.id DESC LIMIT 100", $params);
        foreach ($auditLogs as &$auditLog) {
            $auditLog['data'] = datetime_utc_display((string)($auditLog['created_at'] ?? ''), 'd/m/Y H:i:s');
            unset($auditLog['created_at']);
        }
        unset($auditLog);
        ?>
        <section class="panel">
            <h2>Logs de auditoria</h2>
            <?= table($auditLogs, ['data', 'usuario', 'action', 'resource', 'ip_address']) ?>
        </section>
    <?php });
}

match ($page) {
    'login' => render_login(),
    'dashboard' => render_dashboard(),
    'companies' => render_companies(),
    'plans' => render_plans(),
    'users' => render_users(),
    'teams' => render_teams(),
    'lists' => render_lists(),
    'campaigns' => render_campaigns(),
    'radar' => render_radar(),
    'agent' => render_agent(),
    'supervisor' => render_supervisor(),
    'reports' => render_reports(),
    'recordings' => render_recordings(),
    'costs' => render_costs(),
    'settings' => render_settings(),
    'asterisk_diagnostics' => render_asterisk_diagnostics(),
    'sip_diagnostic' => render_sip_diagnostic(),
    'blocklist' => render_blocklist(),
    'account' => render_account(),
    'audit' => render_audit(),
    default => render_dashboard(),
};
}
