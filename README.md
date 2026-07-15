# Домашнє завдання 8

## Ініціалізація / рестарт проекту

```shell
./init.sh
```

## Зупинити додаток

```shell
kubectl delete -f my-node-app.yaml
```

## Отримати доступ до додатку

```shell
kubectl port-forward svc/my-node-app 8080:80
```

## Перегляд логів (Loki)

Логи збираються автоматично з `stdout` та `stderr` контейнера за допомогою Promtail та надсилаються до Loki.

### Доступ до Grafana

1. Отримати пароль адміністратора:
```shell
kubectl get secret --namespace monitoring loki-grafana -o jsonpath="{.data.admin-password}" | base64 --decode ; echo
```

2. Прокинути порт:
```shell
kubectl port-forward svc/loki-grafana -n monitoring 3000:80
```

3. Відкрити [http://localhost:3000](http://localhost:3000) (User: `admin`).
4. Перейти до **Explore**, обрати **Loki** як джерело даних та використати запит `{app="my-node-app"}`.
