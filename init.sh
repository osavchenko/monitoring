helm repo add grafana https://grafana.github.io/helm-charts
helm repo update

# Install Monitoring Stack (Loki, Promtail, Grafana)
# In a multi-cluster setup, run this in the Monitoring Cluster.
helm upgrade --install loki grafana/loki-stack \
  --namespace monitoring \
  --create-namespace \
  -f values.yaml

# In a multi-cluster setup, run this in the Application Cluster.
# Make sure your kubectl context is set correctly.
kubectl apply -f my-node-app.yaml
