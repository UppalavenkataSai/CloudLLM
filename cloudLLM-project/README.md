# Aviz Academy - AWS & DevOps Learning Platform

A comprehensive 3-tier web application built for AWS capstone project, featuring user management, video streaming via CloudFront, and automated CI/CD deployment.

## 🏗️ Architecture Overview

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   CloudFront    │    │  Application     │    │    Database     │
│   (CDN Layer)   │    │  Load Balancer   │    │   (RDS MySQL)   │
│                 │    │                  │    │                 │
│ • Video Content │    │ • Auto Scaling   │    │ • User Data     │
│ • Static Assets │    │ • Health Checks  │    │ • Sessions      │
│ • Edge Caching  │    │ • SSL/TLS        │    │ • Course Data   │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                    ┌──────────────────┐
                    │   Web Servers    │
                    │   (EC2 + EFS)    │
                    │                  │
                    │ • Apache + PHP   │
                    │ • Shared Storage │
                    │ • Auto Scaling   │
                    └──────────────────┘
```

## 🚀 Features

### Core Functionality
- **User Registration & Authentication**: Secure signup and login system
- **Video Streaming**: CloudFront-powered video delivery for course content
- **Responsive Design**: Modern, mobile-first UI with gradient themes
- **Database Integration**: MySQL RDS with prepared statements for security
- **Session Management**: Secure user session handling

### AWS Services Integration
- **VPC**: Custom networking with public/private subnets
- **EC2**: Auto-scaling web servers with EFS shared storage
- **RDS**: MySQL database with automated backups
- **EFS**: Shared file system for web content
- **CloudFront**: Global content delivery network
- **Application Load Balancer**: High availability and SSL termination
- **Auto Scaling Group**: Dynamic capacity management
- **CodePipeline**: Automated CI/CD deployment

### Security Features
- **SQL Injection Prevention**: Prepared statements and input validation
- **Input Sanitization**: Comprehensive data validation
- **HTTPS Enforcement**: SSL/TLS encryption
- **Database Security**: Limited user privileges and secure connections
- **Session Security**: Secure session token management

## 📁 Project Structure

```
aviz-academy/
├── index.html              # Main landing page
├── newuser.html            # User registration form
├── validation.html         # Login form
├── thankyou.html          # User dashboard
├── video1.html            # AWS Fundamentals course
├── video2.html            # DevOps CI/CD course
├── submit.php             # Registration handler
├── submit2.php            # Login handler
├── config.php             # Configuration and utilities
├── database_setup.sql     # Database schema
├── final.sh              # Deployment script
├── css/
│   └── style.css         # Modern responsive styles
├── js/                   # JavaScript libraries
├── images/               # Static assets and photos
└── README.md            # This file
```

## 🛠️ Installation & Setup

### Prerequisites
- AWS Account with appropriate permissions
- Domain name (optional, for SSL)
- Git repository for CI/CD

### Infrastructure Setup

1. **Create VPC and Networking**
   ```bash
   # VPC: 192.168.0.0/23
   # Subnets:
   # - Web: 192.168.0.0/26, 192.168.0.64/26
   # - App: 192.168.0.128/26, 192.168.0.192/26  
   # - DB: 192.168.1.0/26, 192.168.1.64/26
   ```

dnf install httpd -y
dnf install mariadb105 -y


2. **Launch RDS MySQL Database**
   ```sql
   # Run database_setup.sql to create schema
   mysql -h <rds-endpoint> -u admin -p < database_setup.sql
   ```

3. **Create EFS File System**
   ```bash
   # Mount to /var/www/html/ on EC2 instances
   sudo mount -t nfs4 -o nfsvers=4.1 <efs-id>.efs.<region>.amazonaws.com:/ /var/www/html
   ```

4. **Configure EC2 Instance**
   ```bash
   # Install dependencies
   sudo yum update -y
   sudo yum install -y httpd php php-mysqlnd
   sudo systemctl start httpd
   sudo systemctl enable httpd
   
   # Deploy application files
   sudo cp -r * /var/www/html/
   sudo chmod +x /var/www/html/final.sh
   ```

### Application Configuration

1. **Update Database Configuration**
   ```php
   # Edit config.php with your RDS endpoint
   define('DB_HOST', 'your-rds-endpoint.amazonaws.com');
   define('DB_USERNAME', 'capstoneuser');
   define('DB_PASSWORD', 'your-secure-password');
   ```

2. **Configure CloudFront**
   ```bash
   # Update video URLs in video1.html and video2.html
   # Replace with your CloudFront distribution domain
   ```

3. **Set up CI/CD Pipeline**
   ```yaml
   # CodePipeline configuration
   Source: GitHub repository
   Build: Skip (static content)
   Deploy: EC2 instances with SSM
   ```

## 🔧 Configuration Files

### config.php
Central configuration file containing:
- Database connection settings
- Security configurations
- Utility functions for input validation
- CSRF protection helpers

### database_setup.sql
Complete database schema including:
- User management tables
- Session tracking
- Course progress monitoring
- Proper indexing and constraints

### final.sh
Deployment script that:
- Sets proper file permissions
- Restarts Apache service
- Validates PHP configuration
- Clears application cache

## 🎯 Usage

### For Students
1. **Registration**: Visit `/newuser.html` to create an account
2. **Login**: Use `/validation.html` to access your dashboard
3. **Learning**: Access courses through the dashboard
4. **Progress**: Track your learning journey

### For Administrators
1. **Database Access**: Connect to RDS for user management
2. **Content Updates**: Push changes via CI/CD pipeline
3. **Monitoring**: Use CloudWatch for application metrics
4. **Scaling**: Auto Scaling Group handles traffic spikes

## 🔒 Security Best Practices

### Implemented Security Measures
- **Prepared Statements**: Prevents SQL injection attacks
- **Input Validation**: Sanitizes all user inputs
- **HTTPS Only**: Enforces encrypted connections
- **Session Security**: Secure token generation and validation
- **Database Privileges**: Limited user permissions
- **Error Handling**: Prevents information disclosure

### Recommended Enhancements
- **WAF Integration**: Add AWS WAF for additional protection
- **Rate Limiting**: Implement request throttling
- **Environment Variables**: Move secrets to AWS Systems Manager
- **Audit Logging**: Enable CloudTrail for compliance
- **Backup Strategy**: Automated RDS snapshots

## 📊 Performance Optimization

### Current Optimizations
- **CloudFront CDN**: Global content delivery
- **EFS Caching**: Improved file system performance
- **Database Indexing**: Optimized query performance
- **Auto Scaling**: Dynamic capacity management
- **Load Balancing**: Traffic distribution

### Monitoring & Metrics
- **CloudWatch**: Application and infrastructure monitoring
- **RDS Performance Insights**: Database performance analysis
- **ELB Metrics**: Load balancer health and performance
- **Custom Metrics**: Application-specific monitoring

## 🚀 CI/CD Pipeline

### Pipeline Stages
1. **Source**: GitHub repository integration
2. **Build**: Static content validation (optional)
3. **Deploy**: Automated deployment to EC2 instances
4. **Post-Deploy**: Health checks and validation

### Deployment Process
```bash
# Automated via CodePipeline
git push origin main
# → Triggers pipeline
# → Deploys to staging
# → Runs tests
# → Deploys to production
# → Executes final.sh
```

## 🎓 Learning Outcomes

This project demonstrates proficiency in:
- **AWS Core Services**: EC2, RDS, VPC, EFS, CloudFront
- **DevOps Practices**: CI/CD, Infrastructure as Code
- **Web Development**: PHP, MySQL, responsive design
- **Security**: Best practices for cloud applications
- **Scalability**: Auto-scaling and load balancing
- **Monitoring**: CloudWatch and performance optimization

## 👨‍💻 Author

**Avinash Reddy Thipparthi**
- AWS Community Builder
- Docker Captain  
- Cloud Architect with 14+ years experience
- 7 AWS Certifications
- LinkedIn: [linkedin.com/in/avizway](https://linkedin.com/in/avizway)

## 📄 License

This project is created for educational purposes as part of AWS capstone training at Aviz Academy.

## 🤝 Contributing

This is an educational project. For suggestions or improvements, please reach out through the Aviz Academy platform.

---

*Empowering Cloud Innovators - One Student at a Time* 🚀