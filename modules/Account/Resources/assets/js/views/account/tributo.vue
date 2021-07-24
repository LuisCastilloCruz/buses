<template>
    <div>
        <div class="row">
            <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            </div>

            <div class="card" v-loading="loading">
                <div class="card-header bg-info">
                    <h3 class="my-0">{{ title }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                            <div class="col-md-4">
                                <label>Año</label>
                                <el-date-picker v-model="form.month" type="year"
                                                value-format="yyyy" format="yyyy" :clearable="false"></el-date-picker>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                <tr>
                                    <th>PERIODO</th>
                                    <th>VENTAS NETAS</th>
                                    <th>COMPRAS NETAS</th>
                                    <th>Periodo</th>
                                    <th>Periodo</th>
                                    <th>Periodo</th>
                                </tr>
                                </thead>
                                <tr v-for="(tributo, index) in table" :key="index">
                                    <td>{{tributo.id}}</td>
                                    <td>{{tributo.total_taxed}}</td>
                                    <td>{{tributo.total_igv}}</td>
                                    <td>{{tributo.total}}</td>
                                </tr>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th width="150" class="text-center" rowspan="2">PERIODO</th>
                                    <th width="150" class="text-center" rowspan="2">VENTAS NETAS</th>
                                    <th width="150" class="text-center" rowspan="2">COMPRAS NETAS</th>
                                    <th width="150" class="text-center" colspan="3">IGV</th>
                                    <th width="150" class="text-center" colspan="3">RENTA</th>
                                    <th width="150" class="text-center" colspan="3">PERCEPCIÓN EN EL PERIODO</th>
                                    <th width="150" class="text-center" colspan="3">RETENCIÓN EN EL PERIODO</th>
                                </tr>
                                <tr>
                                    <td width="150" class="text-center">Débito fiscal</td>
                                    <td width="150" class="text-center">Crédito fiscal</td>
                                    <td width="150" class="text-center por-pagar-head">IGV por pagar</td>
                                    <td width="150" class="text-center">Base imponible</td>
                                    <td width="150" class="text-center">
                                    Coeficiente
                                    <a href="/taxes/22206/edit">
                                        [Editar]
                                     </a>        
                                    </td>
                                    <td width="150" class="text-center por-pagar-head">RENTA por pagar</td>
                                    <td width="80" class="text-center">En comprobantes</td>
                                    <td width="80" class="text-center">Con comprobante de percepción</td>
                                    <td width="80" class="text-center">Total</td>
                                    <td width="80" class="text-center">En comprobantes</td>
                                    <td width="80" class="text-center">Con comprobante de retención</td>
                                    <td width="80" class="text-center">Total</td>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    <td width="" class="text-center">01-2021</td>
                                    <td width="" class="text-center">100</td>
                                    <td width="" class="text-center">30</td>
                                    <td width="" class="text-center">18</td>
                                    <td width="" class="text-center">5.4</td>
                                    <td width="" class="text-center por-pagar">12.60</td>
                                    <td width="" class="text-center">100</td>
                                    <td width="" class="text-center">
                                        <a href="/taxes/22206/edit">
                                        0.0150
                                        </a>          
                                    </td>
                                    <td width="" class="text-center por-pagar">1.50</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    </tr>

                                    <tr>
                                    <td width="" class="text-center">02-2021</td>
                                    <td width="" class="text-center">200</td>
                                    <td width="" class="text-center">1000</td>
                                    <td width="" class="text-center">36</td>
                                    <td width="" class="text-center">180</td>
                                    <td width="" class="text-center por-pagar">-144</td>
                                    <td width="" class="text-center">200</td>
                                    <td width="" class="text-center">
                                        <a href="/taxes/22206/edit">
                                        0.0150
                                        </a>          
                                    </td>
                                    <td width="" class="text-center por-pagar">3</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    <td width="" class="text-center">0</td>
                                    </tr>


                                </tbody><tfoot>
                                <tr>
                                    <td width="150" class="text-center">TOTALES</td>
                                    <td width="150" class="text-center">300</td>
                                    <td width="150" class="text-center">1030</td>
                                    <td width="150" class="text-center">54</td>
                                    <td width="150" class="text-center">185.4</td>
                                    <td width="150" class="text-center">-131.40</td>
                                    <td width="150" class="text-center">300</td>
                                    <td width="150" class="text-center">0.030</td>
                                    <td width="150" class="text-center">4.50</td>
                                    <td width="150" class="text-center" colspan="9"></td>
                                </tr>
                                <tr>
                                    <td class="text-center" colspan="19">
                                    <span class="item">*Cuadro referencial.</span>
                                    <a data-confirm="Confima que deseas borrar esta tabla" class="red" rel="nofollow" data-method="delete" href="/taxes/22206">
                                        [Borrar cuadro de tributos]
                            </a>        </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                loading: false,
                loading_submit: false,
                title: null,
                resource: 'account',
                // establishments: [],
                // document_types: [],
                // series: [],
                table:{},
                error: {},
                form: {}
            }
        },
        async created() {
            this.loading = true;
            this.initForm();
            this.title = 'Cuadro de tributos mensuales';
             await this.$http.get(`/${this.resource}/tributo/table`)
                 .then(response => {
                     this.table = response.data.tabla
                 })
             this.loading = false
        },
        methods: {
            initForm() {
                this.errors = {}
                this.form = {
                    year: moment().format('YYYY')
                }
            },
            // resetForm() {
            //     this.form.establishment_id = (this.establishments.length > 0)?this.establishments[0].id:'all'
            // },

            clickDownload() {
                this.loading_submit = true;
                let query = queryString.stringify({
                    ...this.form
                });
                window.open(`/${this.resource}/ple/download?${query}`, '_blank');
                this.loading_submit = false;
            }
        }
    }
</script>
