<template>
	<view>
	       <uni-ec-canvas 
	          class="uni-ec-canvas"
	          id="uni-ec-canvas"
	          ref="uni-ec-canvas"
	          canvas-id="uni-ec-canvas"
	          :ec="ec"
	       ></uni-ec-canvas>
	    </view>
</template>

<script>
	/*export default {
	  onLoad() {
	    
	  },
	  data() {
	    return {
	      
		  }
	  },
	  methods: {
	    
	
	  },
	};*/
	
	import uniEcCanvas from '../../components/uni-ec-canvas/uni-ec-canvas.vue'  
	
	export default{
	    data(){ 
	        return {
	            ec:{
	                option:{
						title: {
					            text: 'ECharts 入门示例'
					            },
					            tooltip: {},
					            legend: {
					                data:['销量']
					            },
					            xAxis: {
					                data: ["衬衫","羊毛衫","雪纺衫","裤子","高跟鞋","袜子"]
					            },
					            yAxis: {},
					            series: [{
					                name: '销量',
					                type: 'bar',
					                data: [5, 20, 36, 10, 10, 20]
					            }]
					} //echart 配置项
	            }
	        }
	    },
	    components:{
	        uniEcCanvas
	    }
	};
</script>

<style>
	.uni-ec-canvas{
	    width:100%;
	    height:100%;
	    display:block;
		}
</style>
