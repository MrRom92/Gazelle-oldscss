#!/bin/bash

set -euo pipefail

MISC_DIR="${CI_PROJECT_DIR}/misc"
SOURCE="${MISC_DIR}/example.local.config.php"
TARGET="${MISC_DIR}/../lib/override.config.php"

[ -f ${TARGET} ] && exit 0
echo "Generating configuration parameters..."
(
    perl -ple 's/""/q{"} . qx(dd if=\/dev\/urandom count=1 bs=12 status=none | base64 | tr -d \\\\n) . q{"}/e' "${SOURCE}"
    date +"define('SITE_LAUNCH_YEAR', %Y);"
    date +"define('TOP_TEN_HISTORY_BEGIN', '%Y-%m-%d');"
    echo "define('IMAGE_CACHE_ENABLED', true);"
) > "${TARGET}"

grep -q SERVER_ROOT "$TARGET" || echo "define('SERVER_ROOT', '$CI_PROJECT_DIR');" >> "${TARGET}"
# mysql
grep -q MYSQL_HOST "$TARGET" || echo "define('MYSQL_HOST', '$MYSQL_HOST');" >> "${TARGET}"
grep -q MYSQL_DB "$TARGET" || echo "define('MYSQL_DB', '$MYSQL_DATABASE');" >> "${TARGET}"
grep -q MYSQL_RO_USER "$TARGET" || echo "define('MYSQL_RO_USER', 'ro_$MYSQL_USER');" >> "${TARGET}"
grep -q MYSQL_RO_PASS "$TARGET" || echo "define('MYSQL_RO_PASS', 'ro_$MYSQL_PASSWORD');" >> "${TARGET}"
grep -q MYSQL_RW_USER "$TARGET" || echo "define('MYSQL_RW_USER', '$MYSQL_USER');" >> "${TARGET}"
grep -q MYSQL_RW_PASS "$TARGET" || echo "define('MYSQL_RW_PASS', '$MYSQL_PASSWORD');" >> "${TARGET}"
sed -i "s|sc5tlc9JSCC6|$MYSQL_ROOT_PASSWORD|g" "${TARGET}"
# postgresql
grep -q PG_HOST "$TARGET" || echo "define('PG_HOST', '$PGHOST');" >> "${TARGET}"
grep -q PG_DB "$TARGET" || echo "define('PG_DB', '$POSTGRES_DATABASE');" >> "${TARGET}"
grep -q PG_RW_USER "$TARGET" || echo "define('PG_RW_USER', '$POSTGRES_DB_USER');" >> "${TARGET}"
grep -q PG_RW_PASS "$TARGET" || echo "define('PG_RW_PASS', '$POSTGRES_USER_PASSWORD');" >> "${TARGET}"
grep -q PG_RO_USER "$TARGET" || echo "define('PG_RO_USER', '$PG_RO_USER');" >> "${TARGET}"
grep -q PG_RO_PASS "$TARGET" || echo "define('PG_RO_PASS', '$PG_RO_PASS');" >> "${TARGET}"
# memcached
grep -q CACHE_ID "$TARGET" || echo "define('CACHE_ID', '$MEMCACHED_NAMESPACE');" >> "${TARGET}"
# sphinx
grep -q SPHINX_HOST "$TARGET" || echo "define('SPHINX_HOST', '127.0.0.1');" >> "${TARGET}"
# tracker
sed -i "s/'DISABLE_TRACKER', true/'DISABLE_TRACKER', false/" "${TARGET}"
grep -q TRACKER_HOST "$TARGET" || echo "define('TRACKER_HOST', '127.0.0.1');" >> "${TARGET}"
grep -q TRACKER_NAME "$TARGET" || echo "define('TRACKER_NAME', 'localhost');" >> "${TARGET}"
grep -q TRACKER_PORT "$TARGET" || echo "define('TRACKER_PORT', '6666');" >> "${TARGET}"
