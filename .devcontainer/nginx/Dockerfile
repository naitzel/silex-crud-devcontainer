FROM node:12 as builder

WORKDIR /app/

RUN npm install -g bower
COPY app/bower.json .
RUN bower install --allow-root

FROM nginx:latest
COPY --from=builder /app/bower_components /var/www/html/public/bin