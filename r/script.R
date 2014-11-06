library(truncnorm)
library(rjson)
#z=rtruncnorm(10000,a=0, b=20, mean=1.15, sd=1.7)
#hist(z, nclass=100)



GetValues <- function(x){
  pit_means <- c(2, 4.5, 5.5, 4, 1.5, 1.8, .65, .05);
  pit_sds <- c(.5, 1.8, 1.8, 1.8, 1, 1.2, .5, .5);
  pit_categories <- c('PU', 'SO', 'GB', 'FB', 'BB', '1B','2B', 'HR')
  
  bat_means <- c(1.15, 1.77, 1.09, 4.7, 6.6, .41, 1.96, .34, 1.98);
  bat_sds <- c(1, .3, .3, 2, 2, .41, 1, .5, 1.8);
  bat_categories <- c('SO', 'GB', 'FB', 'BB', '1B', '1B+', '2B', '3B', 'HR')
  
  # Make sure condition are met
  if(length(pit_means) != length(pit_sds) | length(pit_means) != length(pit_categories) | 
       length(bat_means) != length(bat_sds) | length(bat_means) != length(bat_categories)){
    "The array lengths are not equal. Must quit."
    q("no")
  } else{
    "Array lengths are good."
  }
  
  num <- x
  l <- list()
  
  # Add all chart values to list
  for(i in 1:length(bat_categories)){
    l <- c(l, rtruncnorm(num,a=0, b=20, mean=bat_means[i], sd=bat_sds[i]))
  }
  # then format the list: columns are results, rows are batters
  dim(l) <- c(num,length(bat_categories))
  # turn into matrix that looks exactly the same, but add names
  bat_charts <- matrix(unlist(l), ncol = ncol(l), byrow = FALSE, dimnames=list(NULL, bat_categories))
  remove(l)
  
  # Transform so each chart adds to 20. Now cols are batters, rows are results
  bat_charts <- apply(bat_charts, 1, function(x)(x*20)/sum(x))
  
  # see what statistics are on transformed data, analyze later
  sds <- apply(bat_charts,1,sd)
  avgs <- rowMeans(bat_charts)
  
  #Get ob/con values, since we can't handle them in the matrix already created
  OB <- rtruncnorm(num,a=0, b=20, mean=7.5, sd=1.4)
  C <- rtruncnorm(num,a=0, b=20, mean=3.1, sd=1.2)
  
  # insert the ob values back in
  bat_charts <- rbind(OB, bat_charts)
  
  bat_charts <- toJSON(as.data.frame(t(bat_charts)))
  return(bat_charts)
}

GetValues(5)

