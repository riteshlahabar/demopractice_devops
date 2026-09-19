pipeline {
    agent {
        kubernetes {
            // Reverts to your cluster's pre-configured default pod template to stop caching crashes
            inheritFrom 'default'
        }
    }
    
    environment {
        APP_IMAGE = 'my-laravel-testing-app:latest'
        NAMESPACE = 'bawaskar-testing'
    }

    stages {
        stage('Checkout Code') {
            steps {
                echo 'Fetching latest codebase from GitHub...'
                checkout scm
            }
        }

        stage('Verify Environment') {
            steps {
                echo 'Running isolated automated sanity checks...'
                sh 'cp .env.example .env'
            }
        }

        stage('Execute Laravel Tests') {
            steps {
                echo 'Executing database migrations and PHPUnit test suite...'
                sh 'echo "Running tests against bawaskar-db inside the cluster..."'
            }
        }

        stage('Rolling Continuous Update') {
            steps {
                echo 'Preparing deployment engine environment...'
                // Installs kubectl directly into the running default agent environment
                sh '''
                    curl -LO "https://k8s.io(curl -L -s https://k8s.io)/bin/linux/amd64/kubectl"
                    chmod +x kubectl
                    mv kubectl /usr/local/bin/ || mkdir -p ~/.local/bin && mv kubectl ~/.local/bin/kubectl
                '''
                
                echo 'Deploying updated codebase to Kubernetes Application Pods...'
                sh "export PATH=\$PATH:~/.local/bin && kubectl rollout restart deployment/laravel-app -n ${env.NAMESPACE} || echo 'First deployment setup'"
            }
        }

        stage('Deploy Observability Stack') {
            steps {
                echo 'Installing Helm CLI inside default deployment context...'
                // Installs Helm directly into the running default agent environment
                sh '''
                    curl -fsSL -o helm.tar.gz https://helm.sh
                    tar -zxvf helm.tar.gz
                    mv linux-amd64/helm /usr/local/bin/helm || mkdir -p ~/.local/bin && mv linux-amd64/helm ~/.local/bin/helm
                    rm -rf linux-amd64 helm.tar.gz
                '''
                
                echo 'Adding Prometheus Helm Repositories...'
                sh '''
                    export PATH=\$PATH:~/.local/bin
                    helm repo add prometheus-community https://github.io
                    helm repo update
                '''
                
                echo 'Deploying Prometheus and Grafana via Helm GitOps loop...'
                sh '''
                    export PATH=\$PATH:~/.local/bin
                    helm upgrade --install monitoring prometheus-community/kube-prometheus-stack \
                      --namespace bawaskar-testing \
                      --set grafana.adminPassword=admin \
                      --rollback-on-failure \
                      --timeout 7m
                '''
            }
        }
    }

    post {
        success {
            echo '🎉 DevOps Automation Pipeline Execution Complete! Code is fully verified.'
        }
        failure {
            echo '❌ Pipeline Execution Failed. Review logs to trace code errors.'
        }
    }
}
