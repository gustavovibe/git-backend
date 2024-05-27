terraform {
  backend "s3" {
    bucket = "infra-vibe"
    key    = "terraform/terraform.tfstate"
    region = "us-east-2"
  }
}

module "nginx_api_server_dev" {
  source = "./nginx_api_server_module"
  ami_id        = "ami-09040d770ffe2224f"
  instance_type = "t2.medium"
  server_name   = "nginx-api-server"
  environment   = "develop"
}

output "ng" {
  description = "The public IP address of the Nginx server"
  value       = module.nginx_api_server_dev.ng
}

output "nginx-server-public-dns" {
  description = "The public DNS of the Nginx server"
  value       = module.nginx_api_server_dev.nginx-api-server-public-dns
}