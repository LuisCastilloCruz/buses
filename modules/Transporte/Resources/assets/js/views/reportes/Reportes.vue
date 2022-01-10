<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>REPORTES</span></li>
            </ol>
        </div>
        <div class="card mb-0 mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <el-tabs v-model="tab" >
                            <el-tab-pane label="Reporte ventas por oficina" name="reporte1">
                                <reporte-ventas-por-dia :oficinas="oficinas" />
                            </el-tab-pane>
                            <el-tab-pane label="Reporte Avance de venta por buses" name="reporte2">

                            </el-tab-pane>
                            <el-tab-pane label="Reporte venta por buses" name="reporte3">

                            </el-tab-pane>
                        </el-tabs>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

import ReporteVentasPorDia from './tabs/ReporteVentasPorDia';
export default {
    components:{
        ReporteVentasPorDia
    },
    props:{
        oficinas:{
            type:Array,
            default: () => ([])
        }
    },
    data(){
        return {
            tab: "reporte1",
        }
    }
    
}
</script>