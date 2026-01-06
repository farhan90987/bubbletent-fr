/*
/*Hide chart grids
*/
Chart.defaults.scale.gridLines.display = false;

class chartObject {	 

    labels              = [];
    data                = [];
    backgroundColors    = ['rgb(228,133,58, 0.5)', 'rgb(228,133,58, 0.2)'];
    borderColors        = ['#ffffff', '#ffffff'];

    constructor( target ) 
    {
            this.target = target;
    }

    addData( value, label )
    {   
            if( this.data.length && value > this.data[0] )
            {
                [ this.backgroundColors[0], this.backgroundColors[1]] = [ this.backgroundColors[1], this.backgroundColors[0] ];
            }         

            this.data.push( parseFloat( value.toFixed(2) ) );       
            this.labels.push( label );

    }

    setType( value )
    {        
            this.type = value;
    }

    createDataset()
    {
        return{

            data : this.data,                  
            backgroundColor : this.backgroundColors,
            borderColors : this.borderColors,
            barPercentage : 1,
            dbarThickness : 37,

        }
    }

    print()
    {		   

        let ctx = document.getElementById( this.target ).getContext('2d');		
        
        const dataSet = this.createDataset();
        const type    = this.type;        

        var myChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {				
                labels: this.labels,
                datasets: [dataSet ],            
            },        
            options: {
                legend: {
                    display: false               
                },
                tooltips: {
                    enabled: false
                },
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        color: '#3e3e3e', 
                        anchor: 'end',
                        align: 'end',
                        offset: 10, 
                        clamp:true,                
                        labels: {
                            title: {
                                font: {
                                    weight: 'bold',
                                    size:14
                                },                            
                            },                                                     
                        },
                        formatter: function( value, context) {                          
                            return value +" " + type;
                        }
                    }
                },                
                scales: {
                    xAxes: [{
                        stacked: true,                    
                        ticks: {
                            suggestedMax:100,
                            beginAtZero: true,
                            max: Math.max( ...dataSet.data ) + Math.max( ...dataSet.data ) / 4 ,
                            display: false
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            fontSize: 9,
                            fontColor:'#000'
                        }
                    }]
                }
            },
            plugins: [{
                beforeInit: function(chart) {
                   chart.data.labels.forEach(function(e, i, a) {
                      if (/\n/.test(e)) {
                         a[i] = e.split(/\n/);
                      }
                   });
                }
             }]
        });

        myChart.update();   

    }

}


