# Домашнє завдання 5

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

## Отримати доступ через web-інтерфейс

### Prometheus

```shell
kubectl port-forward svc/monitoring-kube-prometheus-prometheus -n monitoring 9090:9090
```

### Alert Manager

```shell
kubectl port-forward svc/monitoring-kube-prometheus-alertmanager -n monitoring 9093:9093
```

#### Побачити метрики контейнера

Query з суфіксом `{pod="my-node-app"}`

Наприклад: `go_memstats_alloc_bytes{container="my-node-app"}`

### Додаток

```shell
kubectl port-forward svc/my-node-app 8080:80
kubectl port-forward svc/my-node-app 9100:9100
```

## Протестувати alert

### CPU

```shell
N=$(nproc)
for i in $(seq "$N"); do
  yes > /dev/null &
done
```

Зупинити тестування:

```shell
pkill yes
```

### HTTP 500

```shell
SECONDS=0; while [ $SECONDS -lt 300 ]; do curl -s http://localhost:8080/error > /dev/null; sleep 1; done
```

### Disk

```shell
fallocate -l 100G /heavy_test_file.img
```
