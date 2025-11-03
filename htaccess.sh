#!/bin/bash
# --------------------------------------------------------
# 🚀 Auto-generate .htaccess for static file CORS + Cache (with .env support)
# --------------------------------------------------------

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
CONFIG_FILE="$APP_DIR/app/static/Assets.php"
ENV_FILE="$APP_DIR/.env"
OUTPUT_FILE="$APP_DIR/public/.htaccess"

# --------------------------------------------------------
# 🧩 Load .env file (if exists)
# --------------------------------------------------------
if [ -f "$ENV_FILE" ]; then
  echo "🔧 Loading environment from .env"
  export $(grep -v '^#' "$ENV_FILE" | xargs)
fi

# --------------------------------------------------------
# 🧠 Read PHP config as JSON
# --------------------------------------------------------
CONFIG_JSON=$(php -r "echo json_encode(require '$CONFIG_FILE');")

# Extract values from config (with fallback to .env)
ALLOWED_ORIGINS=$(php -r "
    \$config = require '$CONFIG_FILE';
    echo getenv('ASSET_ALLOW_ORIGIN') ?: implode(' ', \$config['allowed_origins']);
")

ALLOWED_METHODS=$(php -r "
    echo implode(', ', (require '$CONFIG_FILE')['allowed_methods']);
")

ALLOWED_HEADERS=$(php -r "
    echo implode(', ', (require '$CONFIG_FILE')['allowed_headers']);
")

CACHE_AGE=$(php -r "
    echo getenv('ASSET_CACHE_AGE') ?: (require '$CONFIG_FILE')['cache_max_age'];
")

FILE_EXTENSIONS=$(php -r "
    echo implode('|', (require '$CONFIG_FILE')['file_extensions']);
")

# --------------------------------------------------------
# 🧩 Generate dynamic ExpiresByType rules
# --------------------------------------------------------
EXPIRES_RULES=$(php -r "
    \$config = require '$CONFIG_FILE';
    \$rules = [];
    foreach (\$config['expires'] as \$type => \$time) {
        if (\$type === 'default') continue;
        \$rules[] = '    ExpiresByType ' . \$type . ' \"' . \$time . '\"';
    }
    echo implode(\"\\n\", \$rules);
")

DEFAULT_EXPIRE=$(php -r "
    echo getenv('ASSET_DEFAULT_EXPIRE') ?: ((require '$CONFIG_FILE')['expires']['default'] ?? 'access plus 1 month');
")

# --------------------------------------------------------
# ✏️ Write the .htaccess file
# --------------------------------------------------------
mkdir -p "$(dirname "$OUTPUT_FILE")"

cat > "$OUTPUT_FILE" <<EOL
# =======================================================
# 🌐 Auto-generated .htaccess for Static Assets (CORS + Cache)
# =======================================================
RewriteEngine On

# -------------------------------------------------------
# 🧭 Default Framework Routing
# -------------------------------------------------------
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L,QSA]

# -------------------------------------------------------
# 🚀 Cache-Control with Expires rules
# -------------------------------------------------------
<IfModule mod_expires.c>
    ExpiresActive On

    # Default cache rule
    ExpiresDefault "$DEFAULT_EXPIRE"

$EXPIRES_RULES
</IfModule>

# -------------------------------------------------------
# 🌐 Static Assets CORS
# -------------------------------------------------------
<IfModule mod_headers.c>
  <FilesMatch "\\.($FILE_EXTENSIONS)\$">
      Header always set Access-Control-Allow-Origin "$ALLOWED_ORIGINS"
      Header always set Access-Control-Allow-Methods "$ALLOWED_METHODS"
      Header always set Access-Control-Allow-Headers "$ALLOWED_HEADERS"
  </FilesMatch>
</IfModule>

# -------------------------------------------------------
# 🚀 Cache Control
# -------------------------------------------------------
<IfModule mod_headers.c>
  <FilesMatch "\\.($FILE_EXTENSIONS)\$">
      Header set Cache-Control "public, max-age=$CACHE_AGE, immutable"
  </FilesMatch>
</IfModule>

# -------------------------------------------------------
# ⚙️ Gzip Compression (if supported)
# -------------------------------------------------------
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css
    AddOutputFilterByType DEFLATE application/javascript application/json
    AddOutputFilterByType DEFLATE font/woff font/woff2
</IfModule>
EOL

echo "✅ .htaccess generated successfully at $OUTPUT_FILE"
