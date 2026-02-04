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
COPY public /var/www/public
COPY --from=assets /app/public/build /var/www/public/build
