<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>kooltivo</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap");
    </style>
    <style>
        .title {
            font-size: 30px;
        }

        .text {
            font-size: 25px;
        }

        .container_1 {
            padding: 5% 5% 0px 5%;
        }

        .presentacion {
            padding: 2% 2% 0px 2%;
        }

        .container_2 {
            padding: 4% 4% 2% 4%;
        }

        .cursos {
            border: solid 1px gainsboro;
            background-color: gainsboro;
            border-radius: 20px;
            padding: 1%;
        }

        .container_3 {
            background-color: #f8b114;
            height: 5%;
            text-align: center;
            padding: 5px;
        }

        .container_4 {
            padding: 0px 5%;
        }

        .nuestra-pagina {
            padding: 5px 2% 2% 2%;
        }

        .img {
            margin-right: 5%;
        }

        @media (max-width: 1440px) {
            .title {
                font-size: 25px;
            }

            .text {
                font-size: 20px;
            }
        }

        @media (max-width: 1024px) {
            .title {
                font-size: 20px;
            }

            .text {
                font-size: 15px;
            }
        }

        @media (max-width: 768px) {
            .title {
                font-size: 15px;
            }

            .text {
                font-size: 10px;
            }
        }

        @media (max-width: 425px) {
            .title {
                font-size: 10px;
            }

            .text {
                font-size: 8px;
            }
        }
    </style>
</head>

<body>
    <header>
        <img style="width: 100%" src="https://aad8012.github.io/imagenes/img/header.png" alt="" />
    </header>
    <main style="font-family: 'Montserrat', sans-serif">
        <div class="container_1" style="text-align: justify">
            <div class="presentacion text">
                <p>Estimad@ {{ $name }}</p>
                <p>
                    Esperamos que se encuentren bien. Nos complace enormemente compartir
                    con ustedes emocionantes novedades que estamos seguros les
                    interesarán. En Fundación Kooltivo, estamos comprometidos con
                    ofrecer oportunidades de aprendizaje de calidad en el campo de la
                    tecnología, y estamos emocionados de presentarles nuestra nueva
                    página web y una serie de cursos excepcionales.
                </p>
            </div>
        </div>
        <div class="container_2">
            <div class="cursos">
                <p class="title" style="font-weight: bold">
                    Nuevos cursos disponibles:
                </p>
                <p style="font-weight: bold" class="text">
                    Estamos encantados de anunciar cuatro emocionantes cursos que
                    estamos ofreciendo:
                </p>
                <div class="title text">
                    <ul>
                        <li>
                            <span style="font-weight: bold"> Microsoft Office:</span>
                            <br />
                            Nuestro curso de Microsoft Office está diseñado para personas de
                            todos los niveles, desde principiantes hasta avanzados.
                            Aprenderás las habilidades fundamentales de Word, Excel y
                            PowerPoint.
                        </li>
                        <p>¡Pueden inscribirse en cualquier momento!</p>
                        <li>
                            <span style="font-weight: bold"> Automation Anywhere:</span>
                            <br />
                            Aprende a automatizar procesos de manera eficiente y robótica
                            con nuestro curso de Automation Anywhere. Descubre cómo
                            transformar tareas rutinarias en eficiencia.
                        </li>
                        <br />
                        <li>
                            <span style="font-weight: bold"> MicroFocus:</span>
                            <br />Prepara a las empresas para la transformación digital con
                            innovación y eficiencia a través de nuestro curso de MicroFocus.
                            Potencia tu experiencia en tecnología y seguridad.
                        </li>
                        <br />
                        <li>
                            <span style="font-weight: bold"> Veeam:</span>
                            <br />
                            Domina la protección y resiliencia de datos como un experto con
                            nuestro curso de Veeam. Aprende sobre respaldo de datos y
                            gestión en la nube.
                        </li>
                    </ul>
                </div>
                <div>
                    <p class="text">
                        <span style="font-weight: bold">Microsoft Office:</span>
                        Inscripciones abiertas en cualquier momento.
                    </p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 50px" class="text">
                Automation Anywhere, MicroFocus y Veeam: Los cursos comienzan el
                <span style="font-weight: bold">25 de este mes.</span>
                <p style="font-weight: bold">¡No hay tiempo que perder!</p>
            </div>
        </div>
        <div class="text container_3">
            <p>
                Te invitamos a revisar los archivos adjuntos de este correo, en donde
                te compartimos la información completa de cada uno de ellos.
            </p>
        </div>
        <div class="container_4">
            <div class="text nuestra-pagina" style="text-align: justify">
                <p>Nuestra nueva página web en construcción:</p>
                <p>
                    Además, estamos emocionados de anunciar que nuestra nueva página web
                    está lista y en espera de publicación. Ésta te brindará una
                    experiencia de navegación más intuitiva y amigable. Mientras tanto,
                    para obtener información detallada sobre cada curso y proceder con
                    su inscripción, puedes contactarnos a través de los siguientes
                    canales.
                </p>
                <p>Contacto:</p>
                <p>
                    Si tienen alguna pregunta, duda o interés en inscripción, no dudes
                    en ponerte en contacto con nuestro equipo en
                    <a href="mailto:correo@example.com">kooltivofundacion@gmail.com;</a>
                    vía
                    <a href="https://wa.me/+52 55 1151 9270?text=Hola">WhatsApp</a> , o
                    por nuestras redes sociales:
                </p>
            </div>
        </div>
        <div></div>
        <div style="text-align: center">
            <a class="img" href="https://www.facebook.com/FundacionKooltivo/?show_switched_toast=0&show_invite_to_follow=0&show_switched_tooltip=0&show_podcast_settings=0&show_community_review_changes=0&show_community_rollback=0&show_follower_visibility_disclosure=0" target="_blank">
                <img src="https://aad8012.github.io/imagenes/img/Pagkooltivo2-07.png" alt="" width="4%" /></a>
            <a class="img" href="https://twitter.com/Kooltivo_" target="_blank">
                <img src="https://aad8012.github.io/imagenes/img/Pagkooltivo2-08.png" alt="" width="4%" /></a>
            <a class="img" href="https://www.instagram.com/kooltivo_/?hl=es-la" target="_blank">
                <img src="https://aad8012.github.io/imagenes/img/Pagkooltivo2-09.png" alt="" width="4%" /></a>
            <a href="https://www.linkedin.com/company/fundación-kooltivo/?viewAsMember=true" target="_blank">
                <img src="https://aad8012.github.io/imagenes/img/in.png" alt="" width="4%" /></a>
        </div>
        <div class="container_2">
            <div class="text cursos">
                <p>
                    En Fundación Kooltivo, estamos comprometidos a brindarte una
                    educación de calidad que te permita destacar en el mundo tecnológico
                    en constante evolución.
                </p>
                <p>
                    No pierdas la oportunidad de adquirir habilidades valiosas y avanzar
                    en tu carrera.
                </p>
                <p style="font-weight: bold; text-align: center">
                    ¡Esperamos verte pronto en nuestros cursos!
                </p>
            </div>
        </div>
    </main>
    <footer>
        <img style="width: 100%" src="https://aad8012.github.io/imagenes/img/footer.png" alt="" />
    </footer>
</body>

</html>