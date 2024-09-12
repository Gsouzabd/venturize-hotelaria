<?php

namespace App\View\Components\Admin;


use Illuminate\View\Component;

class OcupacaoMapa extends Component
{
    public $reservas;
    public $quartos;
    public $dataInicial;
    public $intervaloDias;
    public $action;

    /**
     * Create a new component instance.
     *
     * @param $reservas
     * @param $quartos
     * @param $dataInicial
     * @param $intervaloDias
     * @param $action
     */
    public function __construct($reservas, $quartos, $dataInicial, $intervaloDias, $action)
    {
        $this->reservas = $reservas;
        $this->quartos = $quartos;
        $this->dataInicial = $dataInicial;
        $this->intervaloDias = $intervaloDias;
        $this->action = $action;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.admin.ocupacao-mapa');
    }
}
