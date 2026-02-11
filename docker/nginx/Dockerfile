# Build frontend assets
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js postcss.config.js tailwind.config.js ./
RUN npm ci
COPY resources ./resources
COPY public ./public
RUN npm run build

# Nginx with static files
FROM nginx:alpine

# デフォルトサイト設定を完全に削除
RUN rm -rf /usr/share/nginx/html/* \
    && rm -f /etc/nginx/conf.d/default.conf

# カスタム設定をコピー
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# アプリケーションファイルをコピー
WORKDIR /var/www
COPY . /var/www
COPY --from=assets /app/public/build /var/www/public/build
