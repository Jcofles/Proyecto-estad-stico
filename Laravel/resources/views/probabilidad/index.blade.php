<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Estimación de probabilidad</title>

</head>

<body>

    <h1>Estimar probabilidad</h1>

    <form method="POST" action="{{ route('probabilidad.calcular') }}">

        @csrf

        <label for="numero_de_clientes">Número de clientes:</label>

        <input type="number"

        name="numero_de_clientes"

        id="numero_de_clientes"

        value="{{ old('numero_de_clientes', 10) }}"

        min="1"

        required

        >

        <br><br>



        <label for="probabilidad_de_fallo">

            Probabilidad de fallo

        </label>

        <input type="number"

        step="0.01"

        name="probabilidad_de_fallo"

        id="probabilidad_de_fallo"

        min="0"

        max="1"

        value="{{ old('probabilidad_de_fallo', 0.03) }}"

        required

        >

        <br><br>



        <label for="simulaciones">Número de simulaciones:</label>

        <input type="number"

        name="simulaciones"

        id="simulaciones"

        value="{{ old('simulaciones', 1000) }}"

        min="1"

        required

        >

        <br><br>



        <button type="submit">Calcular</button>

    </form>



        @if($errors->any())

           

                <ul>

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

        @endif



        @isset($resultados)

            <h2>Resultado:</h2>

            <p>Probabilidad Montecarlo:

                 {{ $resultados['probabilidad_montecarlo'] }}

            </p>



            <p>

                Probabilidad de al menos un fallo:

                {{ $resultados['probabilidad_al_menos_un_fallo'] }}

            </p>



            <p>

                Probabilidad sin fallo:

                {{ $resultados['probabilidad_sin_fallo'] }}

            </p>

        @endisset

    </body>

</html>

