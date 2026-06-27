FROM nginx:latest

RUN apt update && apt install prometheus-node-exporter -y

CMD service prometheus-node-exporter start && nginx -g "daemon off;"
