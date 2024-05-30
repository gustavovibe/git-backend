output "ng" {
  description = "The public IP address of the Nginx server"
  value       = aws_instance.nginx-api-server.public_ip
}

output "nginx-api-server-public-dns" {
  description = "The public DNS of the Nginx server"
  value       = aws_instance.nginx-api-server.public_dns
}