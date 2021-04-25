<template>
  <div>
    <div class="page-header pr-0">
      <h2>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
      </h2>
      <ol class="breadcrumbs">
        <li class="active"><span>REGISTRO DE VEHÍCULOS</span></li>
      </ol>
      <div class="right-wrapper pull-right">
        <div class="btn-group flex-wrap">
          <button
            type="button"
            class="btn btn-custom btn-sm mt-2 mr-2"
            @click="onCreateVehiculo"
          >
            <i class="fa fa-plus-circle"></i> Nuevo
          </button>
        </div>
      </div>
    </div>
    <div class="card mb-0">
      <div class="card-header bg-info">
        <h3 class="my-0">Listado de vehiculos</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th></th>
                <th>Placa</th>
                <th>Nombre</th>
                <th>Asientos</th>
                <th>Pisos</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="text-right">{{ item.id }}</td>
                <td>{{ item.placa }}</td>
                <td class="text-center">{{ item.nombre}}</td>
                <td>{{ item.asientos }}</td>
                <td>{{ item.pisos }}</td>
                <td class="text-center">
                  <el-button type="success" @click="onEdit(item)">
                    <i class="fa fa-edit"></i>
                  </el-button>
                  <el-button type="danger" @click="onDelete(item)">
                    <i class="fa fa-trash"></i>
                  </el-button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <ModalAddEdit
      :visible.sync="openModalAddEdit"
      @onAddItem="onAddItem"
      @onUpdateItem="onUpdateItem"
      :vehiculo="vehiculo"
    ></ModalAddEdit>
  </div>
</template>

<script>
import ModalAddEdit from "./AddEdit";

export default {
  props: {
    vehiculos: {
      type: Array,
      required: true,
    },
  },
  components: {
    ModalAddEdit,
  },
  data() {
    return {
      items: [],
      vehiculo: null,
      openModalAddEdit: false,
      loading: false,
    };
  },
  mounted() {
    this.items = this.vehiculos;
  },
  methods: {
    onDelete(item) {
      this.$confirm(`¿estás seguro de eliminar al elemento ${item.description}?`, 'Atención', {
          confirmButtonText: 'Si, continuar',
          cancelButtonText: 'No, cerrar',
          type: 'warning'
        }).then(() => {
          this.$http.delete(`/transportes/vehiculos/${item.id}/delete`).then(response => {
            this.$message({
              type: 'success',
              message: response.data.message
            });
            this.items = this.items.filter(i => i.id !== item.id);
          }).catch(error => {
            this.axiosError(error)
          });
        }).catch();
    },
    onEdit(item) {
      this.vehiculo = { ...item };
      this.openModalAddEdit = true;
    },
    onUpdateItem(data) {
      this.items = this.items.map((i) => {
        if (i.id === data.id) {
          return data;
        }
        return i;
      });
    },
    onAddItem(data) {
      this.items.unshift(data);
    },
    onCreateVehiculo() {
      this.vehiculo = null;
      this.openModalAddEdit = true;
    },
  },
};
</script>
