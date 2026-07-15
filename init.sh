# Add Helm repositories
helm repo add grafana https://grafana.github.io/helm-charts
helm repo update

# Install Tempo (Traces storage)
helm upgrade --install tempo grafana/tempo \
  --namespace monitoring \
  --create-namespace \
  -f tempo-values.yaml

# Install Alloy (Collector)
helm upgrade --install alloy grafana/alloy \
  --namespace monitoring \
  --create-namespace \
  -f alloy-values.yaml

# Install Grafana (Visualization)
helm upgrade --install grafana grafana/grafana \
  --namespace monitoring \
  --create-namespace \
  -f grafana-values.yaml

# Build the PHP App Docker image
docker build -t my-php-app:v2 ./php-app

# Apply the application
kubectl apply -f my-php-app.yaml
kubectl rollout restart deployment my-php-app
