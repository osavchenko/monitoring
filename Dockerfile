FROM nginx:latest

RUN apt update && apt install prometheus-node-exporter -y

COPY nginx/default.conf /etc/nginx/conf.d/default.conf

CMD service prometheus-node-exporter start && nginx -g "daemon off;"
