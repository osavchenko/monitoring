# Домашнє завдання 9

## Initialize / Restart project
This script will install the monitoring stack via Helm, build the local PHP app Docker image, and deploy it to Kubernetes:

```shell
./init.sh
```

## Stop application
```shell
kubectl delete -f my-php-app.yaml
```

## Access Grafana

1. Port-forward Grafana:
```shell
kubectl port-forward svc/grafana -n monitoring 3000:80
```
2. Open http://localhost:3000 in your browser.
3. Get the admin password:
```shell
kubectl get secret --namespace monitoring grafana -o jsonpath="{.data.admin-password}" | base64 --decode ; echo
```

## See Traces in Grafana

1. Go to **Explore** in Grafana.
2. Select **Tempo** from the datasource dropdown.
3. Search for traces or use the Service Graph.

## Access the App

```shell
kubectl port-forward svc/my-php-app 8080:80
```
Generate some traffic: `curl http://localhost:8080`
