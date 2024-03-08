import subprocess

def is_docker_installed():
    try:
        result = subprocess.run(["docker", "--version"], capture_output=True, text=True)
        return "Docker version" in result.stdout
    except FileNotFoundError:
        return False

def install_docker():
    if not is_docker_installed():
        try:
    # Descargar la clave GPG de Docker
            subprocess.run(["curl", "-fsSL", "https://download.docker.com/linux/ubuntu/gpg", "-o", "docker_gpg"], check=True)
    
    # Agregar la clave GPG al sistema
            subprocess.run(["sudo", "apt-key", "add", "docker_gpg"], check=True)
    
    # Configurar el repositorio de Docker en Apt
            subprocess.run(["sudo", "apt-get", "install", "-y", "ca-certificates", "curl"])
            subprocess.run(["sudo", "install", "-m", "0755", "-d", "/etc/apt/keyrings"], check=True)
            subprocess.run(["sudo", "curl", "-fsSL", "https://download.docker.com/linux/ubuntu/gpg", "-o", "/etc/apt/keyrings/docker.asc"], check=True)
            subprocess.run(["sudo", "chmod", "a+r", "/etc/apt/keyrings/docker.asc"], check=True)
            subprocess.run(["echo", "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo \"$VERSION_CODENAME\") stable", "|", "sudo", "tee", "/etc/apt/sources.list.d/docker.list", ">", "/dev/null"], check=True)
    
    # Instalar los paquetes de Docker
            subprocess.run(["sudo", "apt-get", "install", "-y", "docker-ce", "docker-ce-cli", "containerd.io", "docker-buildx-plugin", "docker-compose-plugin"], check=True)
    
    # Agregar el usuario al grupo docker
            subprocess.run(["sudo", "usermod", "-aG", "docker", "ubuntu"], check=True)    

    
            print("Docker se ha instalado correctamente.")
        except subprocess.CalledProcessError as e:
            print(f"Error al instalar Docker: {e}")
    else:
        print("Docker ya está instalado en el sistema.")


if __name__ == "__main__":
    install_docker()

