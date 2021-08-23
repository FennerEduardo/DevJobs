<?php

namespace App\Http\Controllers;

use App\Salario;
use App\Vacante;
use App\Categoria;
use App\Ubicacion;
use App\Experiencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class VacanteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $vacantes = auth()->user()->vacantes;
        $vacantes = Vacante::where('user_id', auth()->user()->id)->latest()->simplePaginate(10);

        return view('vacantes.index', compact('vacantes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Consultas
        $categorias = Categoria::all();
        $experiencias = Experiencia::all();
        $ubicaciones = Ubicacion::all();
        $salarios = Salario::all();

        return view('vacantes.create', compact('categorias', 'experiencias', 'ubicaciones', 'salarios'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255|min:8',
            'categoria' => 'required|integer',
            'experiencia' => 'required|integer',
            'ubicacion' => 'required|integer',
            'salario' => 'required|integer',
            'descripcion' => 'required|string|min:50',
            'imagen' => 'required|string',
            'skills' => 'required|string',
        ]);

        // Almacenar en la DB
        auth()->user()->vacantes()->create([
            'titulo' => $data['titulo'],
            'categoria_id' => $data['categoria'],
            'experiencia_id' => $data['experiencia'],
            'ubicacion_id' => $data['ubicacion'],
            'salario_id' => $data['salario'],
            'descripcion' => $data['descripcion'],
            'imagen' => $data['imagen'],
            'skills' => $data['skills'],
        ]);
        return redirect()->route('vacantes.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Vacante  $vacante
     * @return \Illuminate\Http\Response
     */
    public function show(Vacante $vacante)
    {
        //
        // if(!$vacante->activa) return abort(404);
        return view('vacantes.show', compact('vacante'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Vacante  $vacante
     * @return \Illuminate\Http\Response
     */
    public function edit(Vacante $vacante)
    {
        /**Aplicando el Policy */
        $this->authorize('view', $vacante);
        //Consultas
        $categorias = Categoria::all();
        $experiencias = Experiencia::all();
        $ubicaciones = Ubicacion::all();
        $salarios = Salario::all();

        return view('vacantes.edit', compact('vacante', 'categorias', 'experiencias', 'ubicaciones', 'salarios'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Vacante  $vacante
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vacante $vacante)
    {
        /**Aplicando el Policy */
        $this->authorize('update', $vacante);

        $data = $request->validate([
            'titulo' => 'required|string|max:255|min:8',
            'categoria' => 'required|integer',
            'experiencia' => 'required|integer',
            'ubicacion' => 'required|integer',
            'salario' => 'required|integer',
            'descripcion' => 'required|string|min:50',
            'imagen' => 'required|string',
            'skills' => 'required|string',
        ]);

        $vacante->titulo = $data['titulo'];
        $vacante->categoria_id = $data['categoria'];
        $vacante->experiencia_id = $data['experiencia'];
        $vacante->ubicacion_id = $data['ubicacion'];
        $vacante->salario_id = $data['salario'];
        $vacante->descripcion = $data['descripcion'];
        $vacante->imagen = $data['imagen'];
        $vacante->skills = $data['skills'];
        $vacante->save();

        return redirect()->route('vacantes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Vacante  $vacante
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vacante $vacante)
    {
        /**Aplicando el Policy */
        $this->authorize('delete', $vacante);
        $vacante->delete();
        return response()->json(['mensaje' => 'Se eliminó la vacante ' . $vacante->titulo]);
    }

    /**
     * Store a imagen in storage for a new vacante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function imagen(Request $request)
    {
        $imagen = $request->file('file');
        $nombreImagen = time() . '.' . $imagen->extension();
        $imagen->move(public_path('storage/vacantes'), $nombreImagen);
        return response()->json(['correcto' => $nombreImagen]);
    }
    /**
     * Delete a imagen in storage for a new vacante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function borrarimagen(Request $request)
    {
        if ($request->ajax()) {
            $imagen = $request->get('imagen');
            if (File::exists('storage/vacantes/' . $imagen)) {
                File::delete('storage/vacantes/' . $imagen);
            }

            return response('Imagen Eliminada', 200);
        }
    }

    /**
     * Changes status for a  vacante axios way.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function estado(Request $request, Vacante $vacante)
    {
        // leer nuevo estado y asignarlo
        $vacante->activa = $request->estado;
        //cargarlo a la base de datos
        $vacante->save();
        return response()->json('Actualizada Ok', 200);
    }

    /**
     * Search vacants in the DB.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Vacante  $vacante
     * @return \Illuminate\Http\Response
     */
    public function buscar(Request $request)
    {
        $data = $request->validate([
            'categoria' => 'required|integer',
            'ubicacion' => 'required|integer',
        ]);
        $categoria = $data['categoria'];
        $ubicacion = $data['ubicacion'];
        $vacantes = Vacante::latest()->where('categoria_id', $categoria)->where('ubicacion_id', $ubicacion)->get();
        // $vacantes = Vacante::where([
        //     'categoria_id' => $categoria,
        //     'ubicacion_id' => $ubicacion,
        // ])->get();
        return view('buscar.index', compact('vacantes'));
    }
    /**
     * Search vacants in the DB.
     *
     * @param  \App\Vacante  $vacante
     * @return \Illuminate\Http\Response
     */
    public function resultados()
    {
        return 'Desde resultados';
    }
}
