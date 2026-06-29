# Домашнє завдання 3

## Старт додатку для моніторингу

Зібрати образ (далі `./init.sh` розгорне його як Pod у кластері):

```shell
docker build -f Dockerfile -t my-node-app:latest .
```

## Зупинити додаток
```shell
kubectl delete -f my-node-app.yaml
```

## Ініціалізація / рестарт проекту

```shell
./init.sh
```

## Отримати доступ до Prometheus

```shell
kubectl port-forward svc/monitoring-kube-prometheus-prometheus -n monitoring 9090:9090
```

### Побачити метрики контейнера

Query з суфіксом `{pod="my-node-app"}`

Наприклад: `go_memstats_alloc_bytes{container="my-node-app"}`

## Отримати доступ до додатку

```shell
kubectl port-forward svc/my-node-app 8080:80
kubectl port-forward svc/my-node-app 9100:9100
```
