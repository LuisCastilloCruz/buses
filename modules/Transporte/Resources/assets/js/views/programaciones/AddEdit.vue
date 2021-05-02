<template>
    <el-dialog
        :title="title"
        :visible="visible"
        @close="onClose"
        @open="onCreate"
        width="500px"
    >
        <form autocomplete="off" @submit.prevent="onSubmit">
            <div class="form-body">
                <div class="form-group">
                    <label for="nombre">Origen</label>
                    <el-select placeholder="Seleccionar origen" v-model="form.terminal_origen_id" :class="{ 'is-invalid': errors.destino_id}">
                        <el-option
                            v-for="terminal in terminales"
                            :key="terminal.id"
                            :value="terminal.id"
                            :label="terminal.nombre"
                        ></el-option>
                    </el-select>
                    <!-- <input type="text" name="nombre" id="nombre" class="form-control" v-model="form.terminal_origen_id" :class="{ 'is-invalid': errors.terminal_origen_id }"/> -->
                    <div v-if="errors.terminal_origen_id" class="invalid-feedback">{{ errors.terminal_origen_id[0] }}</div>
                </div>
                <div v-if="form.terminal_origen_id" class="form-group">
                    <label for="direccion">Destino</label>
                    <el-select placeholder="Seleccionar destino" v-model="form.terminal_destino_id" :class="{ 'is-invalid': errors.terminal_destino_id}">
                        <template v-for="terminal in terminales">
                            <el-option
                                v-if="terminal.id != form.terminal_origen_id"
                                :key="terminal.id"
                                :value="terminal.id"
                                :label="terminal.nombre"
                            ></el-option>
                        </template>
                        
                    </el-select>
                    <div v-if="errors.terminal_destino_id" class="invalid-feedback">{{ errors.terminal_destino_id[0] }}</div>
                </div>
                <div class="form-group">
                    <label for="direccion">Vehiculo</label>
                    <el-select placeholder="Seleccionar destino" v-model="form.vehiculo_id" :class="{ 'is-invalid': errors.vehiculo_id}">
                        <template v-for="vehiculo in vehiculos">
                            <el-option
                                :key="vehiculo.id"
                                :value="vehiculo.id"
                                :label="vehiculo.placa"
                            ></el-option>
                        </template>
                        
                    </el-select>
                    <div v-if="errors.vehiculo_id" class="invalid-feedback">{{ errors.vehiculo_id[0] }}</div>
                </div>
                <div class="form-group">
                    <label for="tiempo_aproximado">Tiempo aproximado</label>
                    <el-input
                        type="time"
                        placeholder="Tiempo aproximado"
                        v-model="form.tiempo_aproximado"
                    />
                </div>
                <div class="form-group">
                    <label for="hora_salida">Hora salida</label>
                    <el-input
                        type="time"
                        placeholder="Hora Salida"
                        v-model="form.hora_salida"
                    />
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <el-button
                            native-type="submit"
                            :disabled="loading"
                            type="primary"
                            class="btn-block"
                            :loading="loading"
                        >Guardar</el-button
                        >
                    </div>
                    <div class="col-6">
                        <el-button class="btn-block" @click="onClose">Cancelar</el-button>
                    </div>
                </div>
            </div>
        </form>
    </el-dialog>
</template>

<script>
export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
            default: false,
        },
        programacion: {
            type: Object,
            required: false,
            default: {},
        },
        terminales:{
            type:Array,
            default:() => []
        },
        vehiculos:{
            type:Array,
            default:() => []
        },
    },
    data() {
        return {
            form: {
                terminal_origen_id:null,
                terminal_destino_id:null,
                tiempo_aproximado:null,
                hora_salida:null,
                vehiculo_id:null
            },
            title: "",
            errors: {},
            loading: false,
        };
    },
    methods: {
        async onUpdate() {
            this.loading = true;
            try{
                const { data } =  await this.$http.put(`/transportes/programaciones/${this.programacion.id}/update`,this.form);
                this.$emit("onUpdateItem", data.data);
                this.onClose();
            }catch(error){
                this.axiosError(error);
            }finally{
                this.loading = false;
                this.errors = {};

            }

            // this.$http
            //     .put(`/transportes/terminal/${this.chofer.id}/update`, this.form)
            //     .then((response) => {
            //         this.$emit("onUpdateItem", response.data.data);
            //         this.onClose();
            //     })
            //     .finally(() => {
            //         this.loading = false;
            //         this.errors = {};
            //     })
            //     .catch((error) => {
            //         this.axiosError(error);
            //     });
        },
        async onStore() {
            this.loading = true;
            try{
                const { data } = await this.$http.post("/transportes/programaciones/store",this.form);
                this.$emit("onAddItem", data.data);
                this.onClose();
            } catch(error){
                this.axiosError(error);
            }finally{
                this.loading = false;
                this.errors = {};

            }
            
            // this.$http
            //     .post("/transportes/terminales/store", this.form)
            //     .then((response) => {
            //         this.$emit("onAddItem", response.data.data);
            //         this.onClose();
            //     })
            //     .finally(() => {
            //         this.loading = false;
            //         this.errors = {};
            //     })
            //     .catch((error) => {
            //         this.axiosError(error);
            //     });
        },
        onSubmit() {
            if (this.programacion) {
                this.onUpdate();
            } else {
                this.onStore();
            }
        },
        onClose() {
            this.$emit("update:visible", false);
        },
        onCreate() {
            if (this.programacion) {
                this.form = this.programacion;
                this.title = "Editar programación";
            } else {
                this.title = "Crear programación";
                this.form = {
                    terminal_origen_id:null,
                    terminal_destino_id:null,
                    tiempo_aproximado:null,
                    hora_salida:null,
                    vehiculo_id:null
                };
            }
        },
    },
};
</script>
