# Домашнє завдання 6

## Ініціалізація / рестарт проекту

```shell
./init.sh
```

## Web-доступ

### Prometheus

```shell
kubectl port-forward svc/monitoring-kube-prometheus-prometheus -n monitoring 9090:9090
```

## Побачити метрики

* `probe_success{instance="https://robotdreams.cc"}`
* `probe_duration_seconds{instance="https://robotdreams.cc"}`
* `probe_http_duration_seconds{instance="https://robotdreams.cc"}`
* `probe_http_status_code{instance="https://robotdreams.cc"}`
* `probe_ssl_earliest_cert_expiry{instance="https://robotdreams.cc"} / 86400` # days
* `probe_http_redirects{instance="https://robotdreams.cc"}`
* `probe_dns_lookup_time_seconds{instance="https://robotdreams.cc"}`
* `probe_ip_protocol{instance="https://robotdreams.cc"}`
* `probe_tls_version_info{instance="https://robotdreams.cc"}`
* `probe_http_ssl{instance="https://robotdreams.cc"}`
