resource "aws_security_group" "nginx-api-server-sg" {
  name        = "nginx-server-sg"
  description = "Allow inbound traffic on port 80"

  tags = {
    Name        = "nginx-server-test"
    Environment = "Dev"
    Owner       = "antonio.alonso8012@gmail.com"
    Team        = "SRE-test"
    Project     = "Nginx Server"
  }

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}