<?php


namespace App\Repositories;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;


class ProductRepository{

    public function __construct(){
        $this-> unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
    }

    public function getProducts($nivel2){
        $datos = DB::table('productos')
            ->join("XML", function($join){
                $join->on("productos.productType","=","XML.productType")
                    ->on("productos.brand","=","XML.brand")
                    ->on("productos.mpn","=","XML.mpn");
            })
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType", '=' ,"productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                    ->on("productos.mpn", '=' , "productosCategoriasNivel3.mpn");
            })
            ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
            ->leftJoin("inventario",function($join){
                $join->on("productos.productType","=","inventario.productType")
                    ->on("productos.brand","=","inventario.brand")
                    ->on("productos.mpn","=","inventario.mpn");
            })

            ->select(
                'productos.id','productos.productType', 'productos.brand',
                'productos.mpn', 'productos.description', 'productos.availability',
                'productos.price', 'productos.oferta', 'productos.PrecioDeLista',
                'productos.offer', 'productos.iva', 'productos.video',
                'productos.volada', 'productos.visible',
//                'XML.keywords', 'XML.metadesc', 'XML.descriptionweb', 'XML.titleweb', 'XML.resenia',
                'XML.descriptionweb',
                DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                DB::raw('SUM(inventario.cantidad) as cantidadInventario')
            )
            ->distinct('productos.mpn')
            ->groupBy('productos.productType',
                'productos.brand','productos.mpn')
//                ->orderBy('c3.prioridad', 'asc')
//                ->orderBy('cantidadInventario', 'desc')
//                ->orderByRaw('RAND()')
            ->where([
                "productos.visible" => "si",
                "c3.idCategoriasNivel2" => $nivel2
            ])
            ->inRandomOrder()
            ->get();
        $datos = $this->firstInventarioProducts($datos);
        return $datos;
    }

    public function firstInventarioProducts($products){
        $object =[];
        $count=0;
        foreach ($products as $prod){
            if($prod->cantidadInventario > 0){
                $object[$count]=$prod;
                $count++;
            }
        }
        foreach ($products as $prod){
            if($prod->cantidadInventario == 0){
                $object[$count]=$prod;
                $count++;
            }
        }
        return$object;
    }

    public function getProductsFiltered($nivel2, $filtersLevel3){
        $datos = DB::table('productos')
            ->join("XML", function($join){
                $join->on("productos.productType","=","XML.productType")
                    ->on("productos.brand","=","XML.brand")
                    ->on("productos.mpn","=","XML.mpn");
            })
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType", '=' ,"productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                    ->on("productos.mpn", '=' , "productosCategoriasNivel3.mpn");
            })
            ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
            ->leftJoin("inventario",function($join){
                $join->on("productos.productType","=","inventario.productType")
                    ->on("productos.brand","=","inventario.brand")
                    ->on("productos.mpn","=","inventario.mpn");
            })
            ->select(
                'productos.id','productos.productType', 'productos.brand',
                'productos.mpn', 'productos.description', 'productos.availability',
                'productos.price', 'productos.oferta', 'productos.PrecioDeLista',
                'productos.offer', 'productos.iva', 'productos.video',
                'productos.volada', 'productos.visible',
//                'XML.keywords', 'XML.metadesc', 'XML.descriptionweb', 'XML.titleweb', 'XML.resenia',
                'XML.descriptionweb',
                DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                DB::raw('SUM(inventario.cantidad) as cantidadInventario')
            )
            ->distinct('productos.mpn')
            ->orderBy('cantidadInventario', 'desc')
            ->orderBy('c3.prioridad', 'asc')
            ->where([
                "productos.visible" => "si",
                "c3.idCategoriasNivel2" => $nivel2
            ])
            ->whereIn('c3.idCategoriasNivel3', $filtersLevel3)
            ->groupBy('productos.productType',
                'productos.brand','productos.mpn')
            ->get();

        return $datos;
    }

    public function getProductsOffer(){
        $datos = DB::table('productos')
            ->join("XML", function($join){
                $join->on("productos.productType","=","XML.productType")
                    ->on("productos.brand","=","XML.brand")
                    ->on("productos.mpn","=","XML.mpn");
            })
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                    ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
            })
            ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
//            ->leftJoin('producto_caracteristica as pc', function ($join){
//                $join->on("pc.producto",'=' ,
//                    DB::raw("CONCAT(productos.productType,' ',productos.brand,' ',productos.mpn)"));
//            })
            ->leftJoin("inventario",function($join){
                $join->on("productos.productType","=","inventario.productType")
                    ->on("productos.brand","=","inventario.brand")
                    ->on("productos.mpn","=","inventario.mpn");
            })
            ->select(
                'productos.id',
                'productos.productType',
                'productos.brand',
                'productos.mpn',
                'productos.description',
                'productos.availability',
                'productos.price',
                'productos.oferta',
                'productos.PrecioDeLista',
                'productos.offer',
                'productos.iva',
                'productos.video',
                'productos.volada',
                'productos.visible',
                'XML.descriptionweb',
                DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                DB::raw('SUM(inventario.cantidad) as cantidadInventario')
            )
            ->distinct('productos.mpn')
//            ->orderBy('cantidadInventario', 'desc')
//            ->orderBy('c3.prioridad', 'asc')
            ->where([
                "productos.visible" => "si",
                "productos.availability" => "in stock",
                "productos.offer" => "si"
            ])
            ->groupBy('productos.productType',
                'productos.brand','productos.mpn')
            ->inRandomOrder()
            ->get();
        //descomentar si se quiere quitar el filtro de primero generador y nebulizadora
        $datos = $this->firstProductsOffer($datos);
        return $datos;
    }

    public function firstProductsOffer($products){
        $object =[];
        $count=0;
        foreach ($products as $prod){
            if(trim($prod->productType) == 'Generador' || trim($prod->productType) == 'Nebulizadora' ){
                $object[$count]=$prod;
                $count++;
            }
        }
        foreach ($products as $prod){
            if(trim($prod->productType) != 'Generador' && trim($prod->productType) != 'Nebulizadora' ){
                $object[$count]=$prod;
                $count++;
            }
        }
        return $object;
    }

    public function getProductsFilters($nivel2, $filtros, $cant){
        $valores = array();
        array_push($valores, ["productos.visible", '=', "si"]);
        array_push($valores, ["c3.idCategoriasNivel2", '=', $nivel2]);

        $datos = DB::table('productos')
            ->join("XML", function($join){
                $join->on("productos.productType","=","XML.productType")
                    ->on("productos.brand","=","XML.brand")
                    ->on("productos.mpn","=","XML.mpn");
            })
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType",'=' ,"productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                    ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
            })
            ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
            ->leftJoin("inventario",function($join){
                $join->on("productos.productType","=","inventario.productType")
                    ->on("productos.brand","=","inventario.brand")
                    ->on("productos.mpn","=","inventario.mpn");
            })
            ->select(
                'productos.id',
                'productos.productType',
                'productos.brand',
                'productos.mpn',
                'productos.description',
                'productos.availability',
                'productos.price',
                'productos.oferta',
                'productos.PrecioDeLista',
                'productos.offer',
                'productos.iva',
                'productos.video',
                'productos.volada',
                'productos.visible',
//                'XML.keywords',
//                'XML.metadesc',
                'XML.descriptionweb',
//                'XML.titleweb',
//                'XML.resenia',
                DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                DB::raw('SUM(inventario.cantidad) as cantidadInventario')
            )
            ->distinct('productos.mpn')
            ->orderBy('productos.brand', 'asc')//Se cmabio a brand ya que pc.prodcuto no se consulta
            ->where($valores)
            ->whereRaw($filtros)
            ->groupBy('productos.productType','productos.brand','productos.mpn')
            ->get();
        return $datos;
    }

    public function getProductFiltered($producto, $filtros, $cant){
        $datos = DB::table('producto_caracteristica as pc')
            ->leftJoin('caracteristica as c', 'c.id_caracterisca', '=', 'pc.fk_caracteristica')
            ->select(
                'pc.producto',
                DB::raw('COUNT(pc.producto) AS r')
            )
            ->groupBy('pc.producto')
            ->where(['pc.producto' => $producto])
            ->whereRaw($filtros)
            ->groupBy('pc.producto')
            ->having('r', '=', $cant)
            ->get();

        return count($datos);
    }

    public function getProduct($productType, $brand, $mpn){
        $datos = DB::table('productos')
            ->join("XML",function($join){
                $join->on("productos.productType","=","XML.productType")
                    ->on("productos.brand","=","XML.brand")
                    ->on("productos.mpn","=","XML.mpn");
            })
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=', "productosCategoriasNivel3.brand")
                    ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
            })
            ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
            ->leftJoin('categoriasNivel2', 'categoriasNivel2.idCategoriasNivel2','=','c3.idCategoriasNivel2')
            ->leftJoin('categoriasNivel1', 'categoriasNivel1.idCategoriasNivel1','=','categoriasNivel2.idCategoriasNivel1')
            ->leftJoin("inventario",function($join){
                $join->on("productos.productType","=","inventario.productType")
                    ->on("productos.brand","=","inventario.brand")
                    ->on("productos.mpn","=","inventario.mpn");
            })

            ->select(
                'categoriasNivel2.nombreCategoriaNivel2',
                'categoriasNivel1.nombreCategoriaNivel1',
                'productos.id',
                'productos.productType',
                'productos.brand',
                'productos.mpn',
                'productos.description',
                'productos.availability',
                'productos.price',
                'productos.oferta',
                'productos.PrecioDeLista',
                'productos.offer',
                'productos.iva',
                'productos.video',
                'productos.volada',
                'productos.visible',
                'productos.availability',
                'XML.keywords',
                'XML.metadesc',
                'XML.descriptionweb',
                'XML.titleweb',
                'XML.resenia',
                DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                DB::raw('SUM(inventario.cantidad) as cantidadInventario')
            )
            ->where([
                'productos.productType' => $productType,
                'productos.brand' => $brand,
                'productos.mpn' => $mpn,
                'productos.visible' => 'si',
            ])
            ->groupBy('productos.productType','productos.brand','productos.mpn')
            ->get();

        return $datos;
    }

    public function getProductsRelated($productType, $brand, $mpn){
        $datos = DB::table('productos')
//            ->join("XML",function($join){
//                $join->on("productos.productType","=","XML.productType")
//                    ->on("productos.brand","=","XML.brand")
//                    ->on("productos.mpn","=","XML.mpn");
//            })
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                    ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
            })
            ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
            ->leftJoin("inventario",function($join){
                $join->on("productos.productType","=","inventario.productType")
                    ->on("productos.brand","=","inventario.brand")
                    ->on("productos.mpn","=","inventario.mpn");
            })
            ->select(
                'productos.id',
                'productos.productType',
                'productos.brand',
                'productos.mpn',
                'productos.description',
                'productos.availability',
                'productos.price',
                'productos.oferta',
                'productos.PrecioDeLista',
                'productos.offer',
                'productos.iva',
                'productos.video',
                'productos.volada',
                'productos.visible',
//                'XML.keywords',
//                'XML.metadesc',
//                'XML.descriptionweb',
//                'XML.titleweb',
//                'XML.resenia',
                DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                DB::raw('SUM(inventario.cantidad) as cantidadInventario')
            )
            ->where([
                ['productos.productType', $productType],
                ['productos.kind', 'MAQ'],
                ['productos.visible', 'si'],
                ['productos.mpn', '!=', $mpn]
            ])
            ->whereRaw(
                "productos.price >= (select price * 0.75 as price from productos where productType = '".$productType."' AND brand = '".$brand."' AND mpn = '".$mpn."') AND ".
                "productos.price <= (select price * 1.5 as price from productos where productType = '".$productType."' AND brand = '".$brand."' AND mpn = '".$mpn."')"
            )
            ->groupBy('productos.productType','productos.brand','productos.mpn' )
            ->orderBy('cantidadInventario', 'desc')
            ->limit(8)
            ->get();

        return $datos;
    }

    public function getIdNivel2($nivel1, $nivel2){

        $categoriaNivel1 = str_replace("-", " ", $nivel1);
        if (strtolower($nivel2) != 'hilo nylon'){
            $categoriaNivel2 = str_replace("-", " ", $nivel2);
        }else{
            $categoriaNivel2 = str_replace(" ", "-", $nivel2);
        }


        $query = DB::table('categoriasNivel2')
            ->join('categoriasNivel1', 'categoriasNivel2.idCategoriasNivel1', '=', 'categoriasNivel1.idCategoriasNivel1')
            ->select(
                'categoriasNivel2.idCategoriasNivel2'
            )
            ->where([
                ['categoriasNivel2.nombreCategoriaNivel2', $categoriaNivel2],
                ['categoriasNivel1.nombreCategoriaNivel1', $categoriaNivel1]
            ])
            ->first();
        $idReturn = is_object($query) ? $query->idCategoriasNivel2 : null;
        return $idReturn;
    }

    public function getIdNivel3($nivel1, $nivel2, $nivel3){

        $categoriaNivel1 = str_replace("-", " ", $nivel1);
        if (strtolower($nivel2) != 'hilo nylon'){
            $categoriaNivel2 = str_replace("-", " ", $nivel2);
        }else{
            $categoriaNivel2 = str_replace(" ", "-", $nivel2);
        }
        if (strtolower($nivel3) != 'hilo nylon'){
            $categoriaNivel3 = str_replace("-", " ", $nivel3);
        }else{
            $categoriaNivel3 = str_replace(" ", "-", $nivel3);
        }


        $query = DB::table('categoriasNivel2')
            ->join('categoriasNivel1', 'categoriasNivel2.idCategoriasNivel1', '=', 'categoriasNivel1.idCategoriasNivel1')
            ->join('categoriasNivel3', 'categoriasNivel2.idCategoriasNivel2', '=', 'categoriasNivel3.idCategoriasNivel2')
            ->select(
                'categoriasNivel3.idCategoriasNivel3'
            )
            ->where([
                ['categoriasNivel3.nombreCategoriaNivel3', $categoriaNivel3],
                ['categoriasNivel2.nombreCategoriaNivel2', $categoriaNivel2],
                ['categoriasNivel1.nombreCategoriaNivel1', $categoriaNivel1]
            ])
            ->first();
        $idReturn = is_object($query) ? $query->idCategoriasNivel3 : null;
        return $idReturn;
    }

    public function getCategoriasNivel3($idCategoriaNivel2){
        $categoriasNivel3 = DB::table('categoriasNivel3')
            ->join('categoriasNivel2', 'categoriasNivel3.idCategoriasNivel2', '=', 'categoriasNivel2.idCategoriasNivel2')
            ->join('categoriasNivel1', 'categoriasNivel2.idCategoriasNivel1', '=', 'categoriasNivel1.idCategoriasNivel1')
            ->select(
                'categoriasNivel3.idCategoriasNivel3',
                'categoriasNivel3.nombreCategoriaNivel3',
                'categoriasNivel3.enlace'
            )
            ->where([
                ['categoriasNivel3.idCategoriasNivel2', $idCategoriaNivel2]
            ])
            ->orderBy('categoriasNivel3.prioridad', 'asc')
            ->get();

        return $categoriasNivel3;
    }

    public function getCaracteristicas($productType){
        $filters = DB::table('caracteristica as c')
            ->select(
                'c.nombre',
                'c.medida',
                'c.id_caracterisca',
                'c.tipo'
            )
            ->where([
                ['c.productType', $productType]
            ])
            ->orderBy('c.nombre', 'asc')
            ->get();

        return $filters;
    }

    public function getProductosCaracteristica($id_caracterisca){
        $filters = DB::table('producto_caracteristica as pc')
            ->select(
                'pc.producto as name',
                'pc.valor as value'
            )
            ->where([
                ['pc.fk_caracteristica', $id_caracterisca]
            ])
            ->orderBy('pc.producto', 'asc')
            ->get();

        return $filters;
    }

    public function getProductosCaracteristicaValorMax($id_caracterisca){
        $filters = DB::table('producto_caracteristica as pc')
            ->select(DB::raw('MAX(valor_numero) as valor'))
            ->where([
                ['pc.fk_caracteristica', $id_caracterisca]
            ])
            ->first();

        return $filters->valor;
    }

    public function getProductosCaracteristicaValorMin($id_caracterisca){
        $filters = DB::table('producto_caracteristica as pc')
            ->select(DB::raw('MIN(valor_numero) as valor'))
            ->where([
                ['pc.fk_caracteristica', $id_caracterisca]
            ])
            ->first();

        return $filters->valor;
    }

    public function getProductosCaracteristicaOpciones($id_caracterisca){
        $filters = DB::table('opcion_caracteristica')
            ->select('nombre as name', 'id_opcion_caracteristica as id')
            ->where([
                'fk_caracteristica' => $id_caracterisca
            ])
            ->get();

        return $filters;
    }

    public function getProductlevels($productType){
        $levels = DB::table('categoriasNivel2')
            ->join('categoriasNivel1', 'categoriasNivel2.idCategoriasNivel1', '=', 'categoriasNivel1.idCategoriasNivel1')
            ->select('categoriasNivel2.nombreCategoriaNivel2 as name', 'categoriasNivel1.nombreCategoriaNivel1 as level1')
            ->where([
                ['categoriasNivel2.nombreCategoriaNivel2', 'like', "%$productType%"]
            ])
            ->first();

        return $levels;
    }

    private function clasificarBusqueda($busqueda){
        //Este metodo calcula si alguna de las palabras buscadas tiene mayor coincidencias y regresa eso
        $split = explode(" ", $busqueda);
        $sql = "SELECT productos.id, productos.productType, productos.brand, productos.mpn, productos.description,
        productos.availability,productos.offer, productos.PrecioDeLista, productos.oferta, productos.price,
        productos.visible, productos.iva, productos.video,productos.volada,productos.visible,
        XML.keywords,XML.metadesc,XML.descriptionweb,XML.resenia,XML.titleweb, SUM(inventario.cantidad) as cantidadInventario,
        SUM(productosCategoriasNivel3.priceVisible) as priceVisible
        FROM productos
        join  XML on productos.productType = XML.productType and productos.brand = XML.brand and  productos.mpn = XML.mpn
        left join  inventario on productos.productType = inventario.productType and productos.brand = inventario.brand and  productos.mpn = inventario.mpn

        join productosCategoriasNivel3 on productos.productType = REPLACE(productosCategoriasNivel3.productType,'_',' ')
                    and productos.brand = REPLACE(productosCategoriasNivel3.brand,'_',' ') and productos.mpn = REPLACE(productosCategoriasNivel3.mpn,'_',' ')
        join categoriasNivel3 as c3 on c3.idCategoriasNivel3 = productosCategoriasNivel3.idCategoriasNivel3 ";

        $sqlType = "";
        $sqlMpn = "";
        $sqlBrand = "";
        $banderaWhere = false;

        foreach ($split as $k => $l) {
            if(strlen($l) >= 3){
                $l = ucfirst ($l);
                $sqlType .= $banderaWhere ?" OR ":"";
                $sqlType .= "productos.productType like '%$l%'";
                $banderaWhere = true;
            }
        }
        $banderaWhere = false;
        foreach ($split as $k => $l) {
            if(strlen($l) >= 3){
                $l = ucfirst ($l);
                $sqlMpn .= $banderaWhere ?" OR ":"";
                $sqlMpn .= "productos.mpn like '%$l%'";
                $banderaWhere = true;
            }
        }
        $banderaWhere = false;
        foreach ($split as $k => $l) {
            if(strlen($l) >= 3) {
                $l = ucfirst($l);
                $sqlBrand .= $banderaWhere ? " OR " : "";
                $sqlBrand .= "productos.brand like '%$l%'";
                $banderaWhere = true;
            }
        }

        $groupBy = "group by productos.productType, productos.brand,productos.mpn";
        if($banderaWhere){
            $sql .= " WHERE ";
            $productosType = DB::select( DB::raw($sql . $sqlType . $groupBy) );
            $productosMpn = DB::select( DB::raw($sql . $sqlMpn. $groupBy) );
            $productosBrand = DB::select( DB::raw($sql . $sqlBrand. $groupBy) );

            $productos = array ("brand" => $productosBrand, "type" => $productosType, "mpn" => $productosMpn);
            $productosCant = array ("type" => count($productosType), "mpn" => count($productosMpn), "brand" => count($productosBrand));

            $productosOrdenados = array();
            if($productosCant['mpn'] >= 1){
                $importancia = 1;
                foreach ( $productos['mpn'] as $key => $item) {
                    $productosOrdenados[$importancia][$item->id] = $item;
                }
            }
            if($productosCant['brand'] >= 1){
                foreach ($productos['brand'] as $key => $item) {
                    if(!isset($productosOrdenados[1][$item->id])){
                        if($productosCant['type'] >= 1){
                            foreach ($productos['type'] as $k => $i) {
                                if($item->id == $i->id){
                                    $productosOrdenados[1][$item->id] = $item;
                                }
                            }
                        }else{
                            $productosOrdenados[2][$item->id] = $item;
                        }
                    }
                }
            }
            if($productosCant['type'] >= 1){
                foreach ($productos['type'] as $key => $item) {
                    if(!isset($productosOrdenados[1][$item->id])){
                        if(!isset($productosOrdenados[2][$item->id])){
                            $productosOrdenados[3][$item->id] = $item;
                        }
                    }
                }
            }
        }else{
            $productosOrdenados = array();
        }

        return $productosOrdenados;
    }

    public function sendBusqueda($form, $busqueda){
        $tipo= $busqueda != '' ? 'busqueda':'duda';

        // $email = ['ventas1@jardepot.com','ventas2@jardepot.com','ventas4@jardepot.com','ventas7@jardepot.com','ventas8@jardepot.com'];
        // $num = rand(0,4);
        // $destino = $email[$num];
        $destino = 'ventas10@jardepot.com';
        // $destino = 'sistemas3@jardepot.com';
        $asunto='';
        switch ($tipo){
            case 'duda':
                $asunto='Duda o Comentario de Producto';
                $data = [
                    'nombre' => $form->nombre,
                    'telefono' => $form->telefono,
                    'comentario' => $form->comentario,
                    'whatsapp' => isset($form->whatsapp)?$form->whatsapp:'',
                    'email' => isset($form->email)?$form->email:'',
                    'tipo'=>$tipo,
                    'producto' => isset($form->email)?$form->producto:'Página no encontrada',
                ];
                break;
            case 'busqueda':
                $asunto='Notificación de búsqueda';
                $data = [
                    'nombre' => $form->nombre,
                    'telefono' => $form->telefono,
                    'comentario' => $form->comentario,
                    'tipo'=>$tipo,
                    'busqueda' => $busqueda,
                ];

                break;
            default:
                $asunto='Notificación';
        }

        Mail::send('mails.sendSearchMail', $data, function ($message) use ($asunto, $destino) {
            $message->to($destino)->subject($asunto);
            $message->from('sistemas1@jardepot.com', 'Sistemas Jardepot');
        });

        if( count( Mail::failures() ) > 0 ) {
            return false;
        }
        return true;
    }

    public function getDescriptionNivel2($idNivel2){
        if ($idNivel2 != 0){
            $texto = DB::table('categoriasNivel2')
                ->leftJoin('datosCategoriasNivel2', 'datosCategoriasNivel2.idCategoriasNivel2', '=', 'categoriasNivel2.idCategoriasNivel2')
                ->select('datosCategoriasNivel2.texto',
                    'datosCategoriasNivel2.metadescription',
                    'datosCategoriasNivel2.metatitle',
                    'datosCategoriasNivel2.textH1',
                    'datosCategoriasNivel2.descripcionSitio',
                    'categoriasNivel2.nombreCategoriaNivel2')
                ->where(
                    "categoriasNivel2.idCategoriasNivel2" ,"=",$idNivel2
                )->first();

            if($texto->metatitle == ''){
                $texto->metatitle = 'Encuentra '.$texto->nombreCategoriaNivel2.' de venta en tu tienda en linea.';
            }

            if($texto->metadescription == ''){
                $texto->metadescription = $texto->nombreCategoriaNivel2.' - Catálogo y precios';
            }

            if($texto->texto == ''){
                $texto->texto = $texto->nombreCategoriaNivel2;
            }
            $texto->keywords = $this->singular($texto->nombreCategoriaNivel2);
        }else{
            $texto = DB::table('datosCategoriasNivel2')
                ->select('metadescription','metatitle','metawords as keywords','textH1', 'descripcionSitio')
                ->where(
                    ".idCategoriasNivel2" ,"=",$idNivel2
                )->first();
        }
        return $texto;
    }

    public function getDescriptionLevel3($categoryLevel1, $categoryLevel2, $categoryLevel3){
        $id3= $this->getIdNivel3($categoryLevel1, $categoryLevel2, $categoryLevel3);
        $texto = DB::table('categoriasNivel3')
            ->leftJoin('datosCategoriasNivel3', 'datosCategoriasNivel3.idCategoriasNivel3', '=', 'categoriasNivel3.idCategoriasNivel3')
            ->select('datosCategoriasNivel3.texto',
                'datosCategoriasNivel3.metadescription',
                'datosCategoriasNivel3.metatitle',
                'datosCategoriasNivel3.textH1',
                'categoriasNivel3.nombreCategoriaNivel3 as nombreCategoriaNivel2')
            ->where(
                "categoriasNivel3.idCategoriasNivel3" ,"=",$id3
            )->first();

            if($texto->metatitle == ''){
                $texto->metatitle = 'Encuentra '.$texto->nombreCategoriaNivel2.' de venta en tu tienda en linea.';
            }

            if($texto->metadescription == ''){
                $texto->metadescription = $texto->nombreCategoriaNivel2.' - Catálogo y precios';
            }

            if($texto->texto == ''){
                $texto->texto = $texto->nombreCategoriaNivel2;
            }
            $texto->keywords = $this->singular($texto->nombreCategoriaNivel2);

            return $texto;

    }

    public function getProductsSearch2($busqueda){
        $encontrados = "";
        $matches = array();
        $matchesCount = 0;
        $busqueda2 = "%".$busqueda."%";

        //busqueda simple
        $productos = $this -> clasificarBusqueda($busqueda);

        foreach ($productos as $key => $productoImportancia) {
            foreach ($productoImportancia as $producto) {
                if($producto -> visible == "si"){

                    $matches[$key][$producto -> id] = $producto;
                    $matchesCount ++;

                }
            }
        }//fin de primer while

        $nivelImportancia = 4;

        // buscar separando palabras
        if ($matchesCount < 50) {
            //separa por palabras
            $busqueda = explode(" ", $busqueda);

            foreach ($busqueda as $busqueda2) {

                $busqueda2 = "%".$busqueda2."%";

                $productos = DB::table('productos')
                    ->join("XML", function($join){
                        $join->on("productos.productType","=","XML.productType")
                            ->on("productos.brand","=","XML.brand")
                            ->on("productos.mpn","=","XML.mpn");
                    })
                    ->join("productosCategoriasNivel3", function ($join){
                        $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                            ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                            ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
                    })
                    ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
                    ->leftJoin("inventario",function($join){
                        $join->on("productos.productType","=","inventario.productType")
                            ->on("productos.brand","=","inventario.brand")
                            ->on("productos.mpn","=","inventario.mpn");
                    })
                    ->select(
                        'productos.id',
                        'productos.productType',
                        'productos.brand',
                        'productos.mpn',
                        'productos.description',
                        'productos.availability',
                        'productos.price',
                        'productos.oferta',
                        'productos.PrecioDeLista',
                        'productos.offer',
                        'productos.iva',
                        'productos.video',
                        'productos.volada',
                        'productos.visible',
                        'XML.keywords',
                        'XML.metadesc',
                        'XML.descriptionweb',
                        'XML.titleweb',
                        'XML.resenia',
                        DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                        DB::raw('SUM(inventario.cantidad) as cantidadInventario')
                    )
                    ->distinct('productos.mpn')
                    ->where([
                        ["productos.brand" ,"like",$busqueda2 ]
                    ])
                    ->orWhere([
                        ["productos.visible" , '=',"si"],
                        ["productos.productType","like",$busqueda2 ]
                    ])

                    ->orWhere([
                        ["productos.mpn" ,"like", $busqueda2]
                    ])
                    ->groupBy(
                        'productos.productType',
                        'productos.brand','productos.mpn'
                    )
                    ->get();

                foreach ($productos as $producto) {

                    if($producto -> visible == "si" && !isset($matches[1][$producto -> id]) && !isset($matches[2][$producto -> id]) && !isset($matches[3][$producto -> id])){

                        $matches[4][$producto -> id] = $producto;
                        $matchesCount ++;

                    }
                }
            }
        }


        // buscar quitando letras
        if ($matchesCount < 50) {
            foreach ($busqueda as $busqueda2) {
                //remueve
                $busqueda2 = chop($busqueda2,"es");
                $busqueda2 = chop($busqueda2,"s");
                $busqueda2 = "%".$busqueda2."%";

                $productos = DB::table('productos')
                    ->join("XML", function($join){
                        $join->on("productos.productType","=","XML.productType")
                            ->on("productos.brand","=","XML.brand")
                            ->on("productos.mpn","=","XML.mpn");
                    })
                    ->join("productosCategoriasNivel3", function ($join){
                        $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                            ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                            ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
                    })
                    ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
                    ->leftJoin("inventario",function($join){
                        $join->on("productos.productType","=","inventario.productType")
                            ->on("productos.brand","=","inventario.brand")
                            ->on("productos.mpn","=","inventario.mpn");
                    })
                    ->select('productos.id',
                        'productos.productType',
                        'productos.brand',
                        'productos.mpn',
                        'productos.description',
                        'productos.availability',
                        'productos.price',
                        'productos.oferta',
                        'productos.PrecioDeLista',
                        'productos.offer',
                        'productos.iva',
                        'productos.video',
                        'productos.volada',
                        'productos.visible',
                        'XML.keywords',
                        'XML.metadesc',
                        'XML.descriptionweb',
                        'XML.titleweb',
                        'XML.resenia',
                        DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                        DB::raw('SUM(inventario.cantidad) as cantidadInventario')
                    )
                    ->where([
                        ["productos.brand" ,"like", $busqueda2]
                    ])
                    ->orWhere([
                        ["productos.productType","like", $busqueda2]
                    ])
                    ->orWhere([
                        ["productos.mpn" ,"like", $busqueda2]
                    ])
                    ->groupBy(
                        'productos.productType',
                        'productos.brand','productos.mpn'
                    )
                    ->get();

                foreach ($productos as $producto) {

                    if($producto -> visible == "si" && !isset($matches[1][$producto -> id]) && !isset($matches[2][$producto -> id]) && !isset($matches[3][$producto -> id])){

                        $matches[4][$producto -> id] = $producto;
                        $matchesCount ++;

                    }
                }
            }
        }
        return $matches;
    }

    public function getProductsSearch($busqueda){
        $encontrados = "";
        $matches = array();
        $matchesCount = 0;
        $busqueda2 = "%".$busqueda."%";

        //busqueda simple
        $productos = $this -> clasificarBusqueda($busqueda);

        foreach ($productos as $key => $productoImportancia) {
            foreach ($productoImportancia as $producto) {
                if($producto -> visible == "si"){

                    $matches[$key][$producto -> id]["id"] = $producto -> id;
                    $matches[$key][$producto -> id]["productType"] = $producto -> productType;
                    $matches[$key][$producto -> id]["brand"] = $producto -> brand;
                    $matches[$key][$producto -> id]["mpn"] = $producto -> mpn;
                    $matches[$key][$producto -> id]["description"] = $producto -> descriptionweb;
                    $matches[$key][$producto -> id]["availability"] = $producto -> availability;
                    $matches[$key][$producto -> id]["offer"] = $producto -> offer;
                    $matches[$key][$producto -> id]["PrecioDeLista"] = $producto -> PrecioDeLista;
                    $matches[$key][$producto -> id]["oferta"] = $producto -> oferta;
                    $matches[$key][$producto -> id]["stock"] = $producto -> availability == 'in stock' ?true:false;
                    $matches[$key][$producto -> id]["price"] = $producto -> price;
                    $matches[$key][$producto -> id]["resenia"] = $producto -> resenia;
                    $matches[$key][$producto -> id]["metadesc"] = $producto -> metadesc;
                    $matches[$key][$producto -> id]["titleweb"] = $producto -> titleweb;
                    $matches[$key][$producto -> id]["keywords"] = $producto -> keywords;
                    $matches[$key][$producto -> id]["cantidadInventario"] = $producto -> cantidadInventario;
                    $matchesCount ++;

                }
            }
        }//fin de primer while

        $nivelImportancia = 4;

        // buscar separando palabras
        if ($matchesCount < 50) {
            //separa por palabras
            $busqueda = explode(" ", $busqueda);

            foreach ($busqueda as $busqueda2) {

                $busqueda2 = "%".$busqueda2."%";

                $productos = DB::table('productos')
                    ->join("XML", function($join){
                        $join->on("productos.productType","=","XML.productType")
                            ->on("productos.brand","=","XML.brand")
                            ->on("productos.mpn","=","XML.mpn");
                    })
                    ->join("productosCategoriasNivel3", function ($join){
                        $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                            ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                            ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
                    })
                    ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
                    ->leftJoin("inventario",function($join){
                        $join->on("productos.productType","=","inventario.productType")
                            ->on("productos.brand","=","inventario.brand")
                            ->on("productos.mpn","=","inventario.mpn");
                    })
                    ->select(
                        'productos.id',
                        'productos.productType',
                        'productos.brand',
                        'productos.mpn',
                        'productos.description',
                        'productos.availability',
                        'productos.price',
                        'productos.oferta',
                        'productos.PrecioDeLista',
                        'productos.offer',
                        'productos.iva',
                        'productos.video',
                        'productos.volada',
                        'productos.visible',
                        'XML.keywords',
                        'XML.metadesc',
                        'XML.descriptionweb',
                        'XML.titleweb',
                        'XML.resenia',
                        DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                        DB::raw('SUM(inventario.cantidad) as cantidadInventario')
                    )
                    ->distinct('productos.mpn')
                    ->where([
                        ["productos.brand" ,"like",$busqueda2 ]
                    ])
                    ->orWhere([
                        ["productos.visible" , '=',"si"],
                        ["productos.productType","like",$busqueda2 ]
                    ])

                    ->orWhere([
                        ["productos.mpn" ,"like", $busqueda2]
                    ])
                    ->groupBy(
                        'productos.productType',
                        'productos.brand','productos.mpn'
                    )
                    ->get();

                foreach ($productos as $producto) {

                    if($producto -> visible == "si" && !isset($matches[1][$producto -> id]) && !isset($matches[2][$producto -> id]) && !isset($matches[3][$producto -> id])){

                        $matches[4][$producto -> id]["id"] = $producto -> id;
                        $matches[4][$producto -> id]["productType"] = $producto -> productType;
                        $matches[4][$producto -> id]["brand"] = $producto -> brand;
                        $matches[4][$producto -> id]["mpn"] = $producto -> mpn;
                        $matches[4][$producto -> id]["description"] = $producto -> descriptionweb;
                        $matches[4][$producto -> id]["availability"] = $producto -> availability;
                        $matches[4][$producto -> id]["offer"] = $producto -> offer;
                        $matches[4][$producto -> id]["PrecioDeLista"] = $producto -> PrecioDeLista;
                        $matches[4][$producto -> id]["oferta"] = $producto -> oferta;
                        $matches[4][$producto -> id]["stock"] = $producto -> availability == 'in stock';
                        $matches[4][$producto -> id]["price"] = $producto -> price;
                        $matches[4][$producto -> id]["resenia"] = $producto -> resenia;
                        $matches[4][$producto -> id]["metadesc"] = $producto -> metadesc;
                        $matches[4][$producto -> id]["titleweb"] = $producto -> titleweb;
                        $matches[4][$producto -> id]["keywords"] = $producto -> keywords;
                        $matches[4][$producto -> id]["cantidadInventario"] = $producto -> cantidadInventario;
                        $matches[4][$producto -> id]["priceVisible"] = $producto -> priceVisible;
                        $matchesCount ++;

                    }
                }
            }
        }


        // buscar quitando letras
        if ($matchesCount < 50) {
            foreach ($busqueda as $busqueda2) {
                //remueve
                $busqueda2 = chop($busqueda2,"es");
                $busqueda2 = chop($busqueda2,"s");
                $busqueda2 = "%".$busqueda2."%";

                $productos = DB::table('productos')
                    ->join("XML", function($join){
                        $join->on("productos.productType","=","XML.productType")
                            ->on("productos.brand","=","XML.brand")
                            ->on("productos.mpn","=","XML.mpn");
                    })
                    ->join("productosCategoriasNivel3", function ($join){
                        $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                            ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                            ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
                    })
                    ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
                    ->leftJoin("inventario",function($join){
                        $join->on("productos.productType","=","inventario.productType")
                            ->on("productos.brand","=","inventario.brand")
                            ->on("productos.mpn","=","inventario.mpn");
                    })
                    ->select('productos.id',
                        'productos.productType',
                        'productos.brand',
                        'productos.mpn',
                        'productos.description',
                        'productos.availability',
                        'productos.price',
                        'productos.oferta',
                        'productos.PrecioDeLista',
                        'productos.offer',
                        'productos.iva',
                        'productos.video',
                        'productos.volada',
                        'productos.visible',
                        'XML.keywords',
                        'XML.metadesc',
                        'XML.descriptionweb',
                        'XML.titleweb',
                        'XML.resenia',
                        DB::raw('SUM(inventario.cantidad) as cantidadInventario')
                    )
                    ->where([
                        ["productos.brand" ,"like", $busqueda2]
                    ])
                    ->orWhere([
                        ["productos.productType","like", $busqueda2]
                    ])
                    ->orWhere([
                        ["productos.mpn" ,"like", $busqueda2]
                    ])
                    ->groupBy(
                        'productos.productType',
                        'productos.brand','productos.mpn'
                    )
                    ->get();

                foreach ($productos as $producto) {

                    if($producto -> visible == "si" && !isset($matches[1][$producto -> id]) && !isset($matches[2][$producto -> id]) && !isset($matches[3][$producto -> id])){

                        $matches[4][$producto -> id]["id"] = $producto -> id;
                        $matches[4][$producto -> id]["productType"] = $producto -> productType;
                        $matches[4][$producto -> id]["brand"] = $producto -> brand;
                        $matches[4][$producto -> id]["mpn"] = $producto -> mpn;
                        $matches[4][$producto -> id]["description"] = $producto -> descriptionweb;
                        $matches[4][$producto -> id]["availability"] = $producto -> availability;
                        $matches[4][$producto -> id]["offer"] = $producto -> offer;
                        $matches[4][$producto -> id]["PrecioDeLista"] = $producto -> PrecioDeLista;
                        $matches[4][$producto -> id]["oferta"] = $producto -> oferta;
                        $matches[4][$producto -> id]["stock"] = $producto -> availability == 'in stock' ?true:false;
                        $matches[4][$producto -> id]["price"] = $producto -> price;
                        $matches[4][$producto -> id]["resenia"] = $producto -> resenia;
                        $matches[4][$producto -> id]["metadesc"] = $producto -> metadesc;
                        $matches[4][$producto -> id]["titleweb"] = $producto -> titleweb;
                        $matches[4][$producto -> id]["keywords"] = $producto -> keywords;
                        $matches[4][$producto -> id]["cantidadInventario"] = $producto -> cantidadInventario;
                        $matchesCount ++;

                    }
                }
            }
        }

        $iterator=0;
        $response = array();
        //imprime los resultados de la búsqueda
        foreach ($matches as $matchNivel) {

            foreach ($matchNivel as $key => $match) {
                //solo pone precios si tenemos producto en stock
//                if ($match["availability"] == "in stock") {
                $img = strtolower( $match["productType"] . "-" . $match["brand"] . "-" . $match["mpn"]);

                $response[$iterator]['id'] = $match["id"];
                $response[$iterator]['name'] = $match["productType"] . " " . $match["brand"] . " " . $match["mpn"];
                $response[$iterator]['images'][0]['small'] = 'assets/images/productos/' . $img . '.jpg';
                $response[$iterator]['images'][0]['medium'] = 'assets/images/productos/' . $img . '.jpg';
                $response[$iterator]['images'][0]['big'] = 'assets/images/productos/zoom/' . $img . '.jpg';
                if ($match["offer"] == "si") {
                    $response[$iterator]['discount'] = "Oferta";
                    //solo pone precio de lista cuando es mayor!!
                    if ( $match["PrecioDeLista"] > $match["oferta"]) {
                        $response[$iterator]['oldPrice'] = $match["PrecioDeLista"];
                        $response[$iterator]['newPrice'] = $match["oferta"];
                    }else{
                        $response[$iterator]['newPrice'] = $match["oferta"];
                    }
                    //no está de oferta :(
                }else{
                    //solo pone precio de lista cuando es mayor!!
                    if ($match["PrecioDeLista"] > $match["price"]) {
                        $response[$iterator]['oldPrice'] = $match["PrecioDeLista"];
                        $response[$iterator]['newPrice'] = $match["price"];
                    }else{
                        $response[$iterator]['newPrice'] = $match["price"];
                    }
                }
                $response[$iterator]['description'] = $match["description"];
                $response[$iterator]['dataSheet'] = $match["resenia"];
                $response[$iterator]['availibilityCount'] = 100;
                $response[$iterator]['stock'] = $match['availability'] == 'in stock'  ?true:false ;
                if(isset($match['cantidad'])){
                    $response[$iterator]['cartCount'] = $match["cantidad"];
                }else{
                    $response[$iterator]['cartCount'] = 0;
                }
                $response[$iterator]['brand'] = $match["brand"];
                $response[$iterator]['mpn'] = $match["mpn"];
                $response[$iterator]['productType'] = $match["productType"];

                $response[$iterator]['keywords'] = $match["productType"] . ", " . $match["brand"] . ", " . $match["mpn"];
                if ($match["metadesc"] == ''){
                    $response[$iterator]['metaDescription'] = $match["productType"] . " " . $match["brand"] . " " . $match["mpn"];
                }else{
                    $response[$iterator]['metaDescription'] = $match["metadesc"];
                }
                if ($match["titleweb"] == ''){
                    $response[$iterator]['metaTitle'] = $match["productType"] . " " . $match["brand"] . " " . $match["mpn"];
                }else{
                    $response[$iterator]['metaTitle'] = $match["titleweb"];
                }

                $response[$iterator]['inventory'] = $match["cantidadInventario"];
                $iterator++;
//                }
            }//fin de foreach match
        }
        return $response;
    }

    public function validateImages(){
        $productos = DB::table('productos')
            ->leftJoin("XML", function($join){
                $join->on("productos.productType","=","XML.productType")
                    ->on("productos.brand","=","XML.brand")
                    ->on("productos.mpn","=","XML.mpn");
            })
            ->select(
                'productos.id',
                'productos.productType',
                'productos.brand',
                'productos.mpn',
                'productos.description',
                'productos.availability',
                'productos.price',
                'productos.oferta',
                'productos.PrecioDeLista',
                'productos.offer',
                'productos.iva',
                'productos.video',
                'productos.volada',
                'productos.visible',
                'XML.keywords',
                'XML.metadesc',
                'XML.descriptionweb',
                'XML.titleweb',
                'XML.resenia'
            )
            ->distinct('productos.mpn')
            ->orderBy('productos.productType')
            ->get();
        return $productos;
    }

    public function getIpls($productType, $brand, $mpn){
        $ipl = DB::table('ipl')
            ->select('*')
            ->where([
                'productType' => $productType,
                'brand' => $brand,
                'mpn' => $mpn
            ])->get();

        return $ipl;
    }

    public function singular($pal) {
        $palabraAr = explode(" ", $pal);
        $palabra= strtolower($palabraAr[0]);
        $lng=mb_strlen($palabra,'UTF-8'); // Obtener la longitud de la palabra
        $ultima=mb_substr($palabra,$lng-1,1,'UTF-8');	// Extraer el último carácter
        $penultima=mb_substr($palabra,$lng-2,1,'UTF-8');	// Extraer el penúltimo carácter

        if($ultima =='s' ){
            if ($penultima != 'e' || $palabra == 'aceites' ){
                return substr($palabra,0,-1);
            }else{
                return substr($palabra,0,-2);
            }
        }else{
            return $palabra;
        }
    }

    public function brandExiste($brand){
        $ids = DB::table('productos')
            ->select(
                'productos.id'
            )
            ->where([
                ['productos.brand', $brand]
            ])
            ->get();

        if (count($ids)) {
            return true;
        } else {
            return false;
        }
    }
//Las pruebas pueden elimianrse, solo se hicieron para obtener las imagenes que existen
    function prueba(){
        $ids = DB::table('productos')
            ->join("XML", function($join){
                $join->on("productos.productType","=","XML.productType")
                    ->on("productos.brand","=","XML.brand")
                    ->on("productos.mpn","=","XML.mpn");
            })
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                    ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
            })
            ->select(
                'productos.productType','productos.brand','productos.mpn'
            )
            ->get();
        return $ids;
    }
    function prueba2(){
        $ids = DB::table('productos')
            ->select(
                'productType','brand','mpn'
            )
            ->get();
        return $ids;
    }


    public function getCategoryData($category){
        $categoria = $category;
        $categorias = DB::table('categoriasNivel2')
            ->leftJoin('categoriasNivel1', 'categoriasNivel2.idCategoriasNivel1','=','categoriasNivel1.idCategoriasNivel1')
            ->select('categoriasNivel2.nombreCategoriaNivel2','categoriasNivel2.prioridad')
            ->where('nombreCategoriaNivel1', '=', $categoria)
            ->orderBy('categoriasNivel2.prioridad', 'asc')
            ->get();

        return $categorias;
    }
    public function getProductsByCategory($category){
        $categoria = $category;
        $productos = DB::table('xml')
            ->leftJoin('productoscategoriasnivel3', 'xml.mpn','=','productoscategoriasnivel3.mpn')
            ->leftJoin('categoriasnivel3', 'categoriasnivel3.idCategoriasNivel3','=','productoscategoriasnivel3.idCategoriasNivel3')
            ->leftJoin('categoriasNivel2', 'categoriasNivel2.idCategoriasNivel2','=','categoriasNivel3.idCategoriasNivel2')

            ->leftJoin('categoriasnivel1', 'categoriasnivel1.idCategoriasNivel1','=','categoriasNivel2.idCategoriasNivel1')
            ->where('nombreCategoriaNivel1', '=', $categoria)

            ->get();

            return $productos;

    }
    public function getProductsRelatedByCategory($category){
        $datos = DB::table('productos')
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                    ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
            })
            ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
            ->leftJoin("inventario",function($join){
                $join->on("productos.productType","=","inventario.productType")
                    ->on("productos.brand","=","inventario.brand")
                    ->on("productos.mpn","=","inventario.mpn");
            })
            ->leftJoin('categoriasNivel2', 'categoriasNivel2.idCategoriasNivel2','=','c3.idCategoriasNivel2')
            ->leftJoin('categoriasnivel1', 'categoriasnivel1.idCategoriasNivel1','=','categoriasNivel2.idCategoriasNivel1')
            ->select(
                'productos.id',
                'productos.productType',
                'productos.brand',
                'productos.mpn',
                'productos.description',
                'productos.availability',
                'productos.price',
                'productos.oferta',
                'productos.PrecioDeLista',
                'productos.offer',
                'productos.iva',
                'productos.video',
                'productos.volada',
                'productos.visible',
                DB::raw('SUM(productosCategoriasNivel3.priceVisible) as priceVisible'),
                DB::raw('SUM(inventario.cantidad) as cantidadInventario')
            )
            ->where([
                ['categoriasnivel1.nombreCategoriaNivel1', $category],

            ])
            ->groupBy('productos.productType','productos.brand','productos.mpn' )
            ->orderBy('cantidadInventario', 'desc')
            ->limit(8)
            ->get();

        return $datos;
    }

    public function getBreadCrumbsData($mpn){
        $datos = DB::table('productos')
            ->join("productosCategoriasNivel3", function ($join){
                $join->on("productos.productType",'=' , "productosCategoriasNivel3.productType")
                    ->on("productos.brand",'=' , "productosCategoriasNivel3.brand")
                    ->on("productos.mpn",'=' , "productosCategoriasNivel3.mpn");
            })
            ->join('categoriasNivel3 as c3', 'c3.idCategoriasNivel3', '=', 'productosCategoriasNivel3.idCategoriasNivel3')
            ->leftJoin("inventario",function($join){
                $join->on("productos.productType","=","inventario.productType")
                    ->on("productos.brand","=","inventario.brand")
                    ->on("productos.mpn","=","inventario.mpn");
            })
            ->leftJoin('categoriasNivel2', 'categoriasNivel2.idCategoriasNivel2','=','c3.idCategoriasNivel2')
            ->leftJoin('categoriasNivel1', 'categoriasNivel1.idCategoriasNivel1','=','categoriasNivel2.idCategoriasNivel1')
            ->select(
                'categoriasNivel1.nombreCategoriaNivel1',
                'categoriasNivel2.nombreCategoriaNivel2'
            )
            ->where([
                ['categoriasNivel1.ubicacion', 'navbar'],
                ['productos.mpn',$mpn]
            ])
            ->get();
            return $datos;
    }


}
