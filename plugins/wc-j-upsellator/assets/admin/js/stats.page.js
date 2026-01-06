(function($) {

    $(document).ready( function(){
    
            const upsells_container = {
                'list'  : $('.upsells-list--list'),
                'empty' : $('.upsells-list--empty')
            }

            const gifts_container = {
                'list'  : $('.gifts-list--list'),
                'empty' : $('.gifts-list--empty')
            }           
            
            $(".wc-j-datepicker" ).datepicker({
                    dateFormat : "yy-mm-dd",
                    maxDate: new Date()
            });
    
            $('#getStats').submit()        
    
            $( document ).on( "wooj:stats:received", function( e, data ) {
                    
                    if( !data ) return 
                    
                    $('.currency').html( wc_j_stats.currency )
    
                    if( data.comparison ) $('.comparison-value').fadeIn()
                    else                  $('.comparison-value').fadeOut()  
                    
                    $('.stats-result--list').empty()     
                    
                    maybePrintItems( data.period_1.upsells, upsells_container )
                    maybePrintItems( data.period_1.gifts, gifts_container )                    
                    
                    const charts = populateCharts( data.period_1, data.period_2, data.comparison  )
                    
                    printCharts( charts, data.comparison, data.period_1, data.period_2  )
            })     
    
            function printCharts( charts, comparison, period_1, period_2 )
            {
                    charts.forEach(( element, index ) => {
                            
                            const canvasContainer = $('#' + element.id ).parent('.canvas-container')
    
                            $('#' + element.id ).remove()
                            canvasContainer.html(`<canvas class="stats-chart" id="${element.id}" height="70"></canvas>`)                      
                    
                            const chart                      = new chartObject( element.id ) 
                            
                            chart.addData( element.firstValue, `${period_1.from}\n${period_1.to}`)
    
                            if( comparison )   chart.addData( element.secondValue, `${period_2.from}\n${period_2.to}` )
    
                            chart.setType( element.symbol )
                            chart.print()
                                
                            if( element.vs !== false )
                            {           
                                    if( element.vs >= 0 )       $('.' + element.id + ' .comparison-value').removeClass('neutral').removeClass('negative').addClass('positive')
                                    else if( element.vs == 0 )  $('.' + element.id + ' .comparison-value').removeClass('positive').removeClass('negative').addClass('neutral')
                                    else                        $('.' + element.id + ' .comparison-value').removeClass('neutral').removeClass('positive').addClass('negative')
    
                                    $('.' + element.id + ' .statvalue').html( Math.abs( element.vs.toFixed( wc_j_stats.decimals ) ) )
                            }
                    })
            }
    
            function populateCharts( period1, period2, comparison )
            {       
                    return [
                            
                            { id: "order_total_chart", symbol: wc_j_stats.currency , firstValue: period1.orders_total, secondValue:period2.orders_total, vs:comparison ? calculatePercentage( period2.orders_total, period1.orders_total ) : false },
                            { id: "upsell_total_chart", symbol: wc_j_stats.currency , firstValue: period1.upsell_total, secondValue:period2.upsell_total , vs:comparison ? calculatePercentage( period2.upsell_total, period1.upsell_total ) : false },
                            { id: "order_count_chart", symbol: '', firstValue: period1.orders_count, secondValue:period2.orders_count, vs:comparison ? calculatePercentage( period2.orders_count, period1.orders_count ) : false }, 
                            { id: "total_sold_products_chart", symbol: '', firstValue: period1.items_count, secondValue:period2.items_count, vs:comparison ? calculatePercentage( period2.items_count, period1.items_count ) : false },
                            { id: "total_sold_upsells_chart", symbol: '', firstValue: period1.upsell_count, secondValue:period2.upsell_count, vs:comparison ? calculatePercentage( period2.upsell_count, period1.upsell_count ) : false }, 
                            { id: "average_total_chart", symbol: wc_j_stats.currency , firstValue: period1.order_average, secondValue:period2.order_average, vs:comparison ? calculatePercentage( period2.order_average, period1.order_average ) : false }, 
                            { id: "average_upsell_chart", symbol: wc_j_stats.currency , firstValue: period1.upsell_average, secondValue:period2.upsell_average, vs:comparison ? calculatePercentage( period2.upsell_average, period1.upsell_average ): false },
                            { id: "total_gain_chart", symbol: "%", firstValue: period1.upsell_gain, secondValue:period2.upsell_gain, vs: false },                      
                    ];
            }

            function calculatePercentage( val1, val2 )
            {                
                return parseFloat(  ( val2 - val1 ) / val1 * 100 )               
            }
    
            function maybePrintItems( items, targets )
            {
                    
                    if( items.length )
                    {    
                           
                            targets.empty.addClass('hidden') 

                            printItems( targets.list, items )
    
                            return
    
                    } 
                    
                    targets.empty.removeClass('hidden')  
                    
            }
    
            function printItems( target, items )
            {     
                    items.sort((a,b) => (a.qty > b.qty) ? -1 : 1 )
    
                    let qty = 0
                    let total = 0
                    
                    items.forEach( item => {                        
    
                            target.append(`
                                    <div class="woo-j-stats-row flex-row-between">
                                            <div class="flex-row-start inner-row">
                                                    <div class="image"><img src="${item.thumbnail}"></div>
                                                    <div class="product-name">${item.name}<br>
                                                            <small class="default_price">${item.default_price}${wc_j_stats.currency }</small>
                                                    </div>
                                            </div>
                                            <div class="flex-row-end inner-row">
                                                    <div class="product-qty"><strong>${item.qty}</strong></div>
                                                    <div class="product-total">
                                                            <span class="value">${ parseFloat( item.total ).toFixed( wc_j_stats.decimals )}</span>
                                                            <span class="currency">${wc_j_stats.currency }</span>
                                                    </div>
                                            </div>
                                    </div>
                            `)
    
                            qty   += parseInt( item.qty )
                            total += parseFloat( item.total )    
    
                    })
    
                    target.closest('.stats-result').find('.footer .qty').html( qty )
                    target.closest('.stats-result').find('.footer .total .value').html( total.toFixed( wc_j_stats.decimals ) )
            }
    
    });
})( jQuery );