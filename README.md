CloudLLM — Cloud-hosted LLM Portal

CloudLLM is a scalable, secure web portal for serving Large Language Model (LLM) features (text generation, chat, prompts) hosted on AWS. The project demonstrates cloud architecture best practices: multi-tier VPC, EC2 backend, shared storage (EFS), DB (Aurora RDS), load balancing, Auto Scaling, CDN (CloudFront), DNS (Route 53), security (WAF, ACM), and monitoring/alerts (CloudWatch + SNS). Infrastructure is provisioned via CloudFormation (and optionally Terraform).

💡 Key features

Web UI + REST API to interact with an LLM-backed service

Scalable backend on EC2 behind Application Load Balancer (ALB) and Auto Scaling Group

Shared file storage with EFS (for models, logs, or assets)

Relational DB with Aurora RDS (multi-AZ) for user/meta storage

Static frontend hosted on S3 + CloudFront (SSL via ACM)

DNS with Route 53 and CDN with CloudFront for low-latency global delivery

Security: WAF rules, IAM least-privilege, SSL/TLS

Monitoring & alerts: CloudWatch + SNS notifications

IaC: CloudFormation templates (recommended) + sample Terraform snippets

CI/CD: GitHub Actions pipeline example for build & deploy

Architecture (high level)
Internet
  └─ CloudFront (ACM SSL)
     └─ ALB (Route 53) -> Auto Scaling Group (EC2s)
         ├─ EFS (mounted on EC2)
         └─ Aurora RDS (private subnet)
Static assets -> S3 (CloudFront)
Monitoring -> CloudWatch -> SNS notifications
Security -> WAF + Security Groups + IAM

Tech stack

AWS: VPC, Subnets, EC2, ALB, Auto scaling, EFS, Aurora RDS, S3, CloudFront, Route 53, ACM, WAF, CloudWatch, SNS, CloudFormation

Backend: Python (Flask/FastAPI) or Node.js (Express) — choose one

Frontend: React (static build hosted on S3)

IaC: CloudFormation (primary) and optional Terraform

CI/CD: GitHub Actions

Containerization: Docker (optional for local dev)

Repository structure (suggested)
cloudllm/
├─ infra/                     # CloudFormation / Terraform templates
│  ├─ cloudformation.yml
│  └─ terraform/...
├─ backend/
│  ├─ app.py (or main.js)
│  ├─ requirements.txt (or package.json)
│  └─ Dockerfile
├─ frontend/
│  ├─ src/
│  └─ build/ (generated)
├─ scripts/
│  └─ deploy.sh
├─ README.md
└─ .github/
   └─ workflows/ci-cd.yml

Prerequisites

AWS account with privileges to create VPC, EC2, ALB, EFS, RDS, S3, CloudFront, Route53, ACM, WAF, CloudFormation

AWS CLI configured (aws configure) or CloudFormation console access

Node.js & npm (for frontend) and Python 3.9+ (for backend) installed locally

Docker (optional, for local container testing)

Domain name (optional) if you want Route 53 + ACM SSL setup

Quick start — Local development
1. Clone repo
git clone https://github.com/UppalavenkataSai/cloudllm.git
cd cloudllm

2. Backend (Python example)
cd backend
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
export FLASK_ENV=development
export PORT=8080
export DB_HOST=localhost   # in local dev use sqlite or local docker postgres
python app.py
# OR run via Docker:
docker build -t cloudllm-backend .
docker run -p 8080:8080 --env-file .env cloudllm-backend

3. Frontend (React example)
cd frontend
npm install
npm start         # dev server
# for production build:
npm run build
# upload build/ to S3 for static hosting

4. Test API
curl -X POST http://localhost:8080/api/generate \
  -H "Content-Type: application/json" \
  -d '{"prompt":"Say hello in one sentence"}'

Deploy to AWS — Step by step (CloudFormation recommended)

Note: these steps describe a high-level flow. Use the infra/cloudformation.yml template in this repo and update parameters (VPC cidr, domain, subnets, keypair name, DB password) before launching.

1. Prepare CloudFormation template

infra/cloudformation.yml should create:

VPC with 6 subnets (3 public, 3 private across AZs)

Internet Gateway, NAT Gateway, Route tables

Security Groups for ALB, EC2, RDS

ALB + TargetGroup + Listener (HTTPS)

AutoScalingGroup (EC2 launch config) with user-data to pull backend app from S3/GitHub or Docker image

EFS + MountTargets in private subnets

Aurora RDS cluster in private subnets

S3 bucket for frontend and backend artifacts

CloudFront distribution with ACM certificate

WAF ACL (optional)

CloudWatch log groups and SNS topic

2. Upload artifacts

Build frontend and upload to S3:

cd frontend
npm run build
aws s3 sync build/ s3://<your-frontend-bucket>/ --acl public-read


Build backend Docker image and push to ECR (if using containers), or bundle app to S3 for EC2 to pull and start.

3. Deploy CloudFormation stack
aws cloudformation create-stack --stack-name cloudllm \
  --template-body file://infra/cloudformation.yml \
  --capabilities CAPABILITY_NAMED_IAM \
  --parameters ParameterKey=KeyName,ParameterValue=<ec2-keypair> \
               ParameterKey=DomainName,ParameterValue=example.com


Wait for stack creation completion. Check logs and events in AWS Console > CloudFormation.

4. Configure DNS and SSL

If you provided DomainName and Route53 hosted zone exists, CloudFormation/ACM will request certificate and attach CloudFront/ALB.

Otherwise manually create ACM cert (us-east-1 for CloudFront) and attach.

5. Verify

Visit https://<your-domain> or CloudFront URL to confirm frontend loads.

Test API endpoints through ALB DNS or through custom domain.

Example CloudFormation snippet (ALB + AutoScaling — simplified)
Resources:
  AppLoadBalancer:
    Type: AWS::ElasticLoadBalancingV2::LoadBalancer
    Properties:
      Scheme: internet-facing
      Subnets: !Ref PublicSubnets
      SecurityGroups: [!Ref ALBSecurityGroup]

  AppTargetGroup:
    Type: AWS::ElasticLoadBalancingV2::TargetGroup
    Properties:
      Port: 80
      Protocol: HTTP
      VpcId: !Ref VpcId

  AppListener:
    Type: AWS::ElasticLoadBalancingV2::Listener
    Properties:
      LoadBalancerArn: !Ref AppLoadBalancer
      Port: 443
      Protocol: HTTPS
      Certificates:
        - CertificateArn: !Ref AcmCertificateArn
      DefaultActions:
        - Type: forward
          TargetGroupArn: !Ref AppTargetGroup


(Expand this in infra/cloudformation.yml to include ASG, Launch template, EFS, RDS, etc.)

IAM / Security recommendations

Use least-privilege IAM roles for EC2 and Lambda. Provide only necessary S3/EFS/RDS permissions.

EC2 instances should use instance profile (IAM role) for S3/EFS access; never store AWS keys in code.

Use Security Groups to restrict traffic: ALB allows 443 from internet; EC2 only allows traffic from ALB SG. RDS accepts from app SG only.

Enable encryption at rest (EBS, EFS, RDS) and enforce TLS for connections.

Use WAF to block common attacks (SQLi, XSS) and enable rate-limiting rules as needed.

Monitoring & Alerts

CloudWatch Logs for application logs (use structured JSON logs).

CloudWatch Metrics for CPU, Memory, Latency (custom metrics if needed).

CloudWatch Alarms -> SNS topic to notify on-call (email/Slack).

Enable CloudTrail for auditing API activity.

CI/CD (GitHub Actions — simple example)

.github/workflows/deploy.yml:

name: CI/CD
on:
  push:
    branches: [ main ]
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Build frontend
        run: |
          cd frontend
          npm ci
          npm run build
      - name: Upload to S3
        uses: jakejarvis/s3-sync-action@v0.5.1
        with:
          args: --acl public-read --delete
        env:
          AWS_S3_BUCKET: ${{ secrets.FRONTEND_BUCKET }}
          AWS_REGION: ${{ secrets.AWS_REGION }}
          AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ACCESS_KEY_ID }}
          AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_SECRET_ACCESS_KEY }}


Extend this to build/push backend Docker image to ECR and trigger CloudFormation update/CodeDeploy.

API Endpoints (example)
POST /api/v1/generate
  Body: { "prompt": "Hello", "max_tokens": 100 }
  Response: { "id": "...", "text": "Generated text..." }

GET /api/v1/status
  Response: { "uptime": "...", "instances": 2 }


Provide API documentation (OpenAPI/Swagger) in backend/docs/.

Troubleshooting

Stack creation failed: Check CloudFormation events for resource failures (often IAM, missing AMI, or invalid params).

Backend not serving: SSH to an EC2 instance (if access allowed), check /var/log/cloud-init.log, /var/log/syslog or app logs. Verify user-data script succeeded.

EFS mount issues: Confirm mount targets exist in each AZ and NFS client packages installed. Open NFS ports in SG.

DB connection failures: Ensure RDS is in private subnet and EC2 SG allows outbound to RDS SG. Check credentials and secrets in Secrets Manager or SSM Parameter Store.

Environment variables (example for backend)
APP_ENV=production
PORT=8080
DB_HOST=<aurora-endpoint>
DB_USER=<username>
DB_PASS=<password>         # Use Secrets Manager in production
S3_STATIC_BUCKET=<bucket>
EFS_MOUNT_POINT=/mnt/efs
LOG_LEVEL=INFO

Contributing

Fork the repo

Create a feature branch feature/your-feature

Commit changes & push

Open a Pull Request with description and testing steps

License

MIT License — see LICENSE file.

Contact / Credits

Maintainer: Uppala Venkata Sai (Maran)
Email: uppalavenkatasai@gmail.com
LinkedIn: https://www.linkedin.com/in/uppala-venkata-sai
GitHub: https://github.com/UppalavenkataSai
