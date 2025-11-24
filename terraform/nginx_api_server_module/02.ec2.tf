resource "aws_instance" "nginx-api-server" {
  ami           = var.ami_id
  instance_type = var.instance_type

  user_data = <<-EOF
              #!/bin/bash
              sudo apt-get update
              sudo apt-get install ca-certificates curl
              sudo install -m 0755 -d /etc/apt/keyrings
              sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
              sudo chmod a+r /etc/apt/keyrings/docker.asc
              echo \
                "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
                $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
                sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
                sudo apt-get update
              sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin -y
              sudo usermod -aG docker ubuntu
              sudo apt install python3 python3-pip -y
              EOF

  key_name = aws_key_pair.nginx-api-server-ssh.key_name

  vpc_security_group_ids = [aws_security_group.nginx-api-server-sg.id]

  tags = {
    Name        = var.server_name
    Environment = var.environment
    Owner       = "antonio.alonso8012@gmail.com"
    Team        = "SRE"
    Project     = "Nginx Server"
  }
}
