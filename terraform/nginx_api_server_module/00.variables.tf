variable "ami_id" {
  description = "The AMI ID to use for the instance"
  default     = "ami-09040d770ffe2224f"
}

variable "instance_type" {
  description = "The type of instance to launch"
  default     = "t2.micro"
}

variable "server_name" {
  description = "The name of the server"
  default     = "web-server"
}

variable "environment" {
  description = "The environment in which the server is being deployed"
  default     = "prueba"
}