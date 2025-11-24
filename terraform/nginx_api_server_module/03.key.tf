
resource "aws_key_pair" "nginx-api-server-ssh" {
  key_name   = "nginx-api-server-ssh"
  public_key = file("nginx-api-server.key.pub")

  tags = {
    Name        = "nginx-api-server"
    Environment = "Dev"
    Owner       = "antonio.alonso8012@gmail.com"
    Team        = "SRE"
    Project     = "Nginx Server"
  }
}