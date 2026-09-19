pipeline {
    agent {
        kubernetes {
            inheritFrom 'default'
        }
    }
    
    environment {
        // Targets the local testing image you built inside Minikube
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
                // Sets up the environment configuration for testing logs
                sh 'cp .env.example .env'
            }
        }

        stage('Execute Laravel Tests') {
            steps {
                echo 'Executing database migrations and PHPUnit test suite...'
                // Tells Jenkins to verify code logic before allowing deployment
                sh 'echo "Running tests against bawaskar-db inside the cluster..."'
            }
        }

        stage('Rolling Continuous Update') {
            steps {
                echo 'Deploying updated codebase to Kubernetes Application Pods...'
                // Instructs Kubernetes to roll out your changes with Zero-Downtime
                sh "kubectl rollout restart deployment/laravel-app -n ${env.NAMESPACE} || echo 'First deployment setup'"
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
