new Vue({
 el: '#app',
 data: {
   itens: []
 },

 created: function(){
   setInterval(this.atualiza, 5000);

 },

 methods: {

     atualiza : function(){

     vm = this;
     axios.get('engine.php?class=AtendenteService')
     .then(function (response) {

     vm.itens = response.data;

       console.log(response);
     })
     .catch(function (error) {
       console.log(error);
     })

 }

 }
})
