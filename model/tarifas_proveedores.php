<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2013-2015  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Un autorizado. Puede estar relacionado con un proveedor.
 */ 
class tarifas_proveedores extends fs_model
{
   public $tarifa_id;
   public $tarifa_fecha;
   public $tarifa_codarticulo;   
   public $tarifa_pvp;
   public $tarifa_motivo;
   public $tarifa_codproveedor;
   public $tarifa_agente;

   public function __construct( $p = FALSE)
   {
      parent::__construct('tarifas_proveedor', 'plugins/tarifas_proveedor/');
      
      if($p)
      {
         $this->tarifa_id = $p['tarifa_id'];
         
         $this->tarifa_fecha = NULL;
         if(isset($p['tarifa_fecha']) )
            $this->tarifa_fecha = date('d-m-Y', strtotime($p['tarifa_fecha']));         
         
         $this->tarifa_codarticulo = $p['tarifa_codarticulo'];
         $this->tarifa_pvp = floatval($p['tarifa_pvp']);
         $this->tarifa_motivo = $this->no_html($p['tarifa_motivo']);
         $this->tarifa_codproveedor = $p['tarifa_codproveedor'];
         $this->tarifa_agente = $p['tarifa_agente'];
      }
      else
      {
         $this->tarifa_id = NULL;
         $this->tarifa_fecha = date('d-m-Y');
         $this->tarifa_codarticulo = '';
         $this->tarifa_pvp = 0;
         $this->tarifa_motivo = '';
         $this->tarifa_codproveedor = NULL;
         $this->tarifa_agente = NULL;
      }
   }

   protected function install()
   {
      return '';
   }
   
   public function exists()
   {
      if( is_null($this->tarifa_id) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name."
            WHERE tarifa_id = ".$this->var2str($this->tarifa_id).";");
   } 
   
   public function test()
   {
      $status = FALSE;
      
      $this->tarifa_codproveedor = trim($this->tarifa_codproveedor);
      $this->tarifa_codarticulo = trim($this->tarifa_codarticulo);
      $this->tarifa_pvp = trim($this->tarifa_pvp);
      $this->tarifa_motivo = $this->no_html($this->tarifa_motivo);

      
      if( !preg_match("/^[A-Z0-9]{1,6}$/i", $this->tarifa_codproveedor) )
         $this->new_error_msg("Código de proveedor no válido.");
      else if( strlen($this->tarifa_pvp) < 1 )
         $this->new_error_msg("Precio de tarifa no indicado.");
      else if( strlen($this->tarifa_codarticulo) < 1 )
         $this->new_error_msg("Articulo de tarifa no indicado.");      
      else
         $status = TRUE;
      
      return $status;
   }
   
   public function save() {
       
        if ($this->test()) {            
            if ($this->exists()) return FALSE;
            else {
                $sql = "INSERT INTO `tarifas_proveedor` (`tarifa_fecha`, `tarifa_codarticulo`, `tarifa_pvp`, `tarifa_motivo`, `tarifa_codproveedor`, `tarifa_agente`) 
               VALUES (" . $this->var2str($this->tarifa_fecha) . "," . $this->var2str($this->tarifa_codarticulo) . ",
               " . $this->var2str($this->tarifa_pvp) . "," . $this->var2str($this->tarifa_motivo) . ",
               " . $this->var2str($this->tarifa_codproveedor) . "," . $this->var2str($this->tarifa_agente) . ");";

                if ($this->db->exec($sql)) {
                    $this->tarifa_id = $this->db->lastval();
                    return TRUE;
                } else
                    return FALSE;
            }
        } else
            return FALSE;
    }    
      
   public function all($offset=0, $limit=FS_ITEM_LIMIT) {
        $tarifas = array();
        
        $sql = "SELECT * FROM tarifas_proveedor WHERE 1 ORDER BY tarifa_fecha DESC";
        
        $data = $this->db->select_limit($sql, $limit, $offset);
        
        if ($data) {
            foreach ($data as $d)
                $tarifas[] = new tarifas_proveedores($d);
        }

        return $tarifas;       
   }    

   public function delete()
   {
       return $this->db->exec("DELETE FROM tarifas_proveedor WHERE tarifa_id = ".$this->var2str($this->tarifa_id).";");
   }
   
  public function get($id)
   {
      $sql = "SELECT * FROM `tarifas_proveedor` WHERE tarifa_id = " . $this->var2str($id) . ";";
        
      $data = $this->db->select($sql);
      
      if($data)
         return new tarifas_proveedores($data[0]);
      else
         return FALSE;       
   } 
   
   public function get_tarifas_proveedor($id)
   {
      $tarifas = array();
        
      $sql = "SELECT * FROM `tarifas_proveedor` WHERE tarifa_codproveedor = " . $this->var2str($id) . " ORDER BY tarifa_codarticulo, tarifa_fecha DESC;";
      
      $data = $this->db->select($sql);
      
      if ($data) {
          foreach ($data as $d)
              $tarifas[] = new tarifas_proveedores($d);
      }      
      
      return $tarifas;
   }
   public function get_tarifas_proveedor_select($id)
   {
      $tarifas = array();

      $sql = "SELECT * FROM tarifas_proveedor WHERE tarifa_id IN (SELECT max(tarifa_id) FROM tarifas_proveedor WHERE tarifa_codproveedor = " . $this->var2str($id)." GROUP BY tarifa_codarticulo ) ORDER BY tarifa_fecha DESC;";
      
      $data = $this->db->select($sql);
      
      if ($data) {
          foreach ($data as $d)
              $tarifas[] = new tarifas_proveedores($d);
      }      
      
      return $tarifas;
   }
   
   public function nombre_articulo(){
       
       $date= $this->db->select("SELECT descripcion FROM articulos WHERE referencia =".$this->var2str($this->tarifa_codarticulo).";");
        
       if ($date) return $date[0];
       else return FALSE;
   } 

   
}


