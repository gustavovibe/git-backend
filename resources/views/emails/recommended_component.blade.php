<head>
    <style>
        /* Estilo de las tarjetas */
        .card {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
            height: 25rem;
            box-sizing: border-box;
            overflow: hidden; /* Evita que los elementos dentro de la tarjeta se desborden */
        }

        .card img {
            max-width: 80%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .card h5 {
            margin-top: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 1rem;
            color: gray;
            margin-bottom: 15px;
        }

        /* Estilo de los botones */
        .card a {
            display: inline-flex;
            align-items: center;
            color: white;
            font-weight: bold;
            text-decoration: none;
            margin-top: 15px;
            background-color: orange;
            padding: 10px 20px;
            border-radius: 5px;
            justify-content: center;
            height: 40px; /* Altura fija para los botones */
            width: 100%; /* El botón ocupará todo el ancho del contenedor */
            max-width: 100%; /* Asegura que no se expanda más allá de los límites */
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Imagen dentro del botón */
        .card a img {
            width: 18px; /* Tamaño fijo para la imagen dentro del botón */
            height: auto;
            margin-left: 10px;
        }

        /* Contenedor principal */
        .Recomend #container {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 3%;
            background-color: rgba(0, 128, 0, 0.1);
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Estilos generales */
        .Recomend h2 {
            text-align: center;
            font-size: 1.8rem;
        }

        .Recomend p {
            text-align: center;
            color: gray;
            font-size: 12px;
            margin-bottom: 20px;
        }

        /* Estilos de la tabla */
        .card-table {
            width: 100%;
            table-layout: fixed;
            border-spacing: 10px;
            overflow-x: auto; /* Permite que la tabla tenga un scroll horizontal si es necesario */
            box-sizing: border-box;
        }

        .card-table td {
            width: 24%; /* Fija el ancho de cada celda */
            padding: 10px;
            vertical-align: top;
            box-sizing: border-box;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .card-table td {
                width: 100%; /* En pantallas más pequeñas, apilar las tarjetas */
                display: block;
                text-align: center;
                padding: 10px;
            }

            .card h5 {
                font-size: 1rem; /* Reducir el tamaño del título en pantallas pequeñas */
            }

            .card p {
                font-size: 0.9rem; /* Reducir el tamaño del texto en pantallas pequeñas */
            }

            .card a {
                padding: 10px 20px; /* Asegura que el padding sea consistente */
                font-size: 1rem; /* Mantener el tamaño de texto adecuado */
                min-width: 200px; /* Mantener un ancho mínimo adecuado para el botón */
                max-width: 100%; /* Asegura que el botón se ajuste al contenedor */
            }

            .card img {
                max-width: 60%; /* Reducir el tamaño de la imagen */
            }

            /* Reducir el padding y márgenes de todo el contenedor */
            .Recomend #container {
                padding: 5%;
            }
        }

        /* Pantallas muy pequeñas (como móviles en vertical) */
        @media (max-width: 480px) {
            .card h5 {
                font-size: 0.9rem; /* Reducir aún más el tamaño del título */
            }

            .card p {
                font-size: 0.8rem; /* Reducir aún más el tamaño del texto */
            }

            .card a {
                padding: 8px 16px; /* Hacer que el botón sea más pequeño */
                font-size: 0.9rem; /* Reducir aún más el tamaño del texto del botón */
                min-width: 150px; /* Asegurar que los botones no sean demasiado pequeños */
                max-width: 100%; /* Asegura que el botón no se salga del card */
            }

            .card img {
                max-width: 50%; /* Hacer la imagen aún más pequeña */
            }

            /* Reducir padding y márgenes para pantallas más pequeñas */
            .Recomend #container {
                padding: 4%;
            }
        }
    </style>
</head>
<body style="padding: 5%;">
    <div style="page-break-before: always;" class="Recomend">
        <div id="container">
            <h2>Recommended</h2>
            <p>Adding these services to your trip now can save you money compared to purchasing them later or in the destination.</p>

            <!-- Contenedor para la tabla, asegurando que se adapte a la pantalla -->
            <div style="overflow-x:auto;">
                <!-- Tabla para los cards -->
                <table class="card-table">
                    <tr>
                        <td>
                            <div class="card">
                                <div style="text-align: center">
                                    <img src="https://vibeadventures.be/images/transfer.png" alt="Airport Transfer">
                                </div>
                                <h5>Airport transfer</h5>
                                <p>Airport transfers not included in adventure?</p>
                                <a href="#">Get Transfers <img  style="width: 5%;height:5%" src="https://vibeadventures.be/images/box-arrow-up-right.png" alt="Icon"></a>
                            </div>
                        </td>
                        <td>
                            <div class="card">
                                <div style="text-align: center">
                                    <img src="https://vibeadventures.be/images/insurance.png" alt="Insurance">
                                </div>
                                <h5>Insurance</h5>
                                <p>Available up to 24h before departure</p>
                                <a href="#">Manage Insurance <img  style="width: 5%;height:5%" src="https://vibeadventures.be/images/box-arrow-up-right.png" alt="Icon"></a>
                            </div>
                        </td>
                        <td>
                            <div class="card">
                                <div style="text-align: center">
                                    <img src="https://vibeadventures.be/images/accommodation.png" alt="Accommodation">
                                </div>
                                <h5>Accommodation</h5>
                                <p>Need pre- or post-tour accommodation?</p>
                                <a href="#">Book Accommodation <img  style="width: 5%;height:5%" src="https://vibeadventures.be/images/box-arrow-up-right.png" alt="Icon"></a>
                            </div>
                        </td>
                        <td>
                            <div class="card">
                                <div style="text-align: center">
                                    <img src="https://vibeadventures.be/images/activities.png" alt="Activities">
                                </div>
                                <h5>Activities</h5>
                                <p>Got extra days in the destination before or after the adventure?</p>
                                <a href="#">Find Activities <img style="width: 5%;height:5%" src="https://vibeadventures.be/images/box-arrow-up-right.png" alt="Icon"></a>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
