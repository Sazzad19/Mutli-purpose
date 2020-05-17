<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isAdminOrAuthor()">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Users Table</h3>

                <div class="card-tools">
                    <button class="btn btn-success" @click="newModal">Add User<i class="fas fa-user-plus fa-fw"></i></button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Type</th>
                      <th>Registered At</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in users.data" :key="user.id">
                      <td>{{user.id}}</td>
                      <td>{{user.name}}</td>
                      <td>{{user.email}}</td>
                      <td>{{user.type | toUpper }}</td>
                      <td>{{user.created_at | myDate }}</td>
                      <td>
                          <a href="#" @click="editUser(user)">
                              <i class="fa fa-edit"></i>
                          </a>
                          /
                          <a href="#" @click="deleteUser(user.id)">
                              <i class="fa fa-trash"></i>
                          </a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="users" @pagination-change-page="getResults"></pagination>
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>
        <div lass="row mt-5" v-if="!$gate.isAdminOrAuthor()">
          <not-found></not-found>
        </div>
<!-- Modal -->
<div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 v-show="!editmode" class="modal-title" id="addNew">Add User</h5>
        <h5 v-show="editmode" class="modal-title" id="addNew">Edit User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form @submit.prevent="editmode ? updateUser() : createUser()">
      <div class="modal-body">

    <div class="form-group">
      <input v-model="form.name" placeholder="Name" type="text" name="name"
        class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
      <has-error :form="form" field="name"></has-error>
    </div>

    <div class="form-group">
      <input v-model="form.email" placeholder="Email" type="email" name="email"
        class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
      <has-error :form="form" field="email"></has-error>
    </div>

    <div class="form-group">
      <select v-model="form.type" type="text" name="type"
        class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
        <option value="">Select User Type</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
        <option value="author">Author</option>
      </select>
      <has-error :form="form" field="type"></has-error>
    </div>

    <div class="form-group">
      <textarea v-model="form.bio" placeholder="Short Bio For user (Optional)" type="text" name="bio"
        class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
      <has-error :form="form" field="bio"></has-error>
    </div>

    <div class="form-group">

      <input v-model="form.password" placeholder="Password" type="password" name="password"
        class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
      <has-error :form="form" field="password"></has-error>
    </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button v-show="!editmode" type="submit" class="btn btn-primary">Create</button>
        <button v-show="editmode" type="submit" class="btn btn-success">Update</button>
      </div>
      </form>
    </div>
  </div>
</div>
    </div>
</template>

<script>
    export default {
        data () {
          return {
            editmode: false,
            users: {},
           form: new Form({
             id: '',
             name: '',
             email: '',
             password: '',
             type: '',
             bio: '',
             photo: 'profile.jpg'
           })
         }
       },
       methods:{
         getResults(page = 1) {
           axios.get('api/users?page=' + page)
           .then(response => { this.users = response.data; });
           },
         loadUsers() {
           if(this.$gate.isAdminOrAuthor()){
           axios.get('api/users').then(({ data }) => (this.users = data));
           }
         },

         newModal() {
           this.form.reset();
           this.editmode = false;
           $("#addNew").modal('show');
           
          },
         createUser() {
           this.$Progress.start();
           this.form.post('/api/users')
           .then(() => {
             Fire.$emit('afterCreated');
             $("#addNew").modal('hide');

             Toast.fire({
             icon: 'success',        
             title: 'Created successfully'
             });

             this.$Progress.finish();
             })
             .catch(() => {})
           
              },
         deleteUser(id) {

           Swal.fire({
              title: 'Are you sure?',
              text: "You won't be able to revert this!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
              if (result.value) {
                this.form.delete('api/users/'+id)
                .then(() => {
                  Swal.fire(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success'
                )
                Fire.$emit('afterCreated');
                })
                .catch(() => {
                  Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Something went wrong!'
                  })
                })
                
              }
            })
         },
         editUser(data) {
           this.form.reset();
           this.editmode = true;
           $("#addNew").modal('show');
           this.form.fill(data);
         },
         updateUser() {
           this.$Progress.start();
           this.form.put('api/users/'+this.form.id)
           .then(() => {
             Fire.$emit('afterCreated');
             $("#addNew").modal('hide');
             Swal.fire(
                  'Updated!',
                  'Information has been updated.',
                  'success'
                )
                this.$Progress.finish();

           })
           .catch()
         }
       },
        created() {
            this.loadUsers();
            Fire.$on('afterCreated', () => { this.loadUsers() });
            //setInterval(() => this.loadUsers(), 3000);
            Fire.$on('searchiing', () => {
              let query = this.$parent.searchItem;
              axios.get('api/search?q='+query)
              .then(({data}) => {
                this.users = data;
              })
              .catch()
            })
        }
    }
</script>
