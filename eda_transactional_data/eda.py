from requests import head
import streamlit as st
import pandas as pd
import numpy as np
import plotly.express as px

st.text("Basic data analysis")

# read csv file with columns:
# client_id,gender,birth_date,create_date,nonresident_flag,businessman_flag,city,term,contract_sum,product_category_name,card_id,card_type_name,start_date,fact_close_date,purchase_sum,purchase_count,current_balance_avg_sum,current_balance_sum,current_debit_turn_sum,current_credit_turn_sum,card_type 
raw_data = pd.read_csv('experiments/eda/data.csv')



# fig = raw_data['city'].value_counts().plot.pie().get_figure()
# st.pyplot(fig)

st.write(raw_data.columns)

city_info = raw_data['city'].value_counts().reset_index().rename(columns={
    'city': 'count',
    'index': 'city'
})
# st.write(city_info)

# Count users by sity
fig = px.pie(city_info.head(25), values='count', names='city', title='Users count by city')
fig.update_traces(textposition='inside', textinfo='percent+label')
st.plotly_chart(fig, use_container_width=True)


# Count sum purchase by city and avg purchase by city to new dataframe
city_purchase = raw_data.groupby('city').agg({
    'purchase_sum': 'sum',
    'current_balance_avg_sum': 'mean'
}).reset_index()

# Count sum purchases by city to new dataframe and render it as pie chart
fig1 = px.pie(city_purchase, names='city', values='purchase_sum', title='Purchase sum by city')
fig1.update_traces(textposition='inside', textinfo='percent+label')
st.plotly_chart(fig1, use_container_width=True)

# Count average purcase by city to new dataframe and render it as pie chart
avg_purchase_by_city = raw_data.groupby('city').agg({'purchase_sum': 'mean'}).reset_index() 
fig2 = px.pie(avg_purchase_by_city.head(20), names='city', values='purchase_sum', title='Average purchase sum by city')
fig2.update_traces(textposition='inside', textinfo='percent+label')
st.plotly_chart(fig2, use_container_width=True)

# Show top 10 cities with most average balance
top_10_cities_with_avg_balance = raw_data.groupby('city').agg({'current_balance_avg_sum': 'mean'}).reset_index()
top_10_cities_with_avg_balance = top_10_cities_with_avg_balance.sort_values(by='current_balance_avg_sum', ascending=False)
top_10_cities_with_avg_balance = top_10_cities_with_avg_balance.head(10)
fig3 = px.pie(top_10_cities_with_avg_balance, names='city', values='current_balance_avg_sum', title='Top 10 cities with most average balance')
fig3.update_traces(textposition='inside', textinfo='value+label')
st.plotly_chart(fig3, use_container_width=True)


# Get current year
current_year = pd.datetime.now().year
raw_data['age'] = current_year - raw_data['birth_date']

ages_info = raw_data['age'].value_counts().reset_index().rename(columns={
    'age': 'count',
    'index': 'age'
})
# Sort ages_info by count
ages_info = ages_info.sort_values(by='count', ascending=False)

# Render ages info as pie chart
fig4 = px.pie(ages_info, values='count', names='age', title='Users count by age')
fig4.update_traces(textposition='inside', textinfo='percent+label')
st.plotly_chart(fig4, use_container_width=True)

# Render ages info as vertical bar chart
fig5 = px.bar(ages_info, x='age', y='count', title='Users count by age')
st.plotly_chart(fig5, use_container_width=True)

# Count average purchase by age to new dataframe and render it as pie chart
avg_purchase_by_age = raw_data.groupby('age').agg({'purchase_sum': 'mean'}).reset_index()
# Sort avg_purchase_by_age by purchase_sum
avg_purchase_by_age = avg_purchase_by_age.sort_values(by='purchase_sum', ascending=False)
fig6 = px.pie(avg_purchase_by_age.head(20), names='age', values='purchase_sum', title='Average purchase sum by age')
fig6.update_traces(textposition='inside', textinfo='value+label')
st.plotly_chart(fig6, use_container_width=True)
# Render average purchase by age to new dataframe and render it as vertical bar chart
fig7 = px.bar(avg_purchase_by_age, x='age', y='purchase_sum', title='Average purchase sum by age')
st.plotly_chart(fig7, use_container_width=True)

# Count average purchase count by age to new dataframe, sort it and render it as pie chart
avg_purchase_count_by_age = raw_data.groupby('age').agg({'purchase_count': 'mean'}).reset_index()
# Sort avg_purchase_count_by_age by purchase_count  
avg_purchase_count_by_age = avg_purchase_count_by_age.sort_values(by='purchase_count', ascending=False)
fig8 = px.pie(avg_purchase_count_by_age.head(20), names='age', values='purchase_count', title='Average purchase count by age')
fig8.update_traces(textposition='inside', textinfo='value+label')
st.plotly_chart(fig8, use_container_width=True)
# Render average purchase count by age to new dataframe and render it as vertical bar chart
fig9 = px.bar(avg_purchase_count_by_age, x='age', y='purchase_count', title='Average purchase count by age')
st.plotly_chart(fig9, use_container_width=True)

# Count average balance by age to new dataframe and render it as pie chart
avg_balance_by_age = raw_data.groupby('age').agg({'current_balance_avg_sum': 'mean'}).reset_index()
avg_balance_by_age = avg_balance_by_age.sort_values(by='current_balance_avg_sum', ascending=False)

fig10 = px.pie(avg_balance_by_age.head(20), names='age', values='current_balance_avg_sum', title='Average balance sum by age')
fig10.update_traces(textposition='inside', textinfo='value+label')
st.plotly_chart(fig10, use_container_width=True)
# Render average balance by age to new dataframe and render it as vertical bar chart
fig11 = px.bar(avg_balance_by_age, x='age', y='current_balance_avg_sum', title='Average balance sum by age')
st.plotly_chart(fig11, use_container_width=True)

# Count average balance by age and card type to new dataframe and render it as vertical bar chart
avg_balance_by_age_and_card_type = raw_data.groupby(['age', 'card_type']).agg({'current_balance_avg_sum': 'mean'}).reset_index()
avg_balance_by_age_and_card_type = avg_balance_by_age_and_card_type.sort_values(by='current_balance_avg_sum', ascending=False)
fig12 = px.bar(avg_balance_by_age_and_card_type, x='age', y='current_balance_avg_sum', color='card_type', title='Average balance sum by age and card type')
st.plotly_chart(fig12, use_container_width=True)

# Count average purchase sum by age and card type to new dataframe and render it as vertical bar chart
avg_purchase_sum_by_age_and_card_type = raw_data.groupby(['age', 'card_type']).agg({'purchase_sum': 'mean'}).reset_index()
avg_purchase_sum_by_age_and_card_type = avg_purchase_sum_by_age_and_card_type.sort_values(by='purchase_sum', ascending=False)
fig13 = px.bar(avg_purchase_sum_by_age_and_card_type, x='age', y='purchase_sum', color='card_type', title='Average purchase sum by age and card type')
st.plotly_chart(fig13, use_container_width=True)

# Count average purchase count by age and card type to new dataframe and render it as vertical bar chart
avg_purchase_count_by_age_and_card_type = raw_data.groupby(['age', 'card_type']).agg({'purchase_count': 'mean'}).reset_index()
avg_purchase_count_by_age_and_card_type = avg_purchase_count_by_age_and_card_type.sort_values(by='purchase_count', ascending=False)
fig14 = px.bar(avg_purchase_count_by_age_and_card_type, x='age', y='purchase_count', color='card_type', title='Average purchase count by age and card type')
st.plotly_chart(fig14, use_container_width=True)

# Count sum of purchases by age and card type to new dataframe and render it as vertical bar chart
sum_purchases_by_age_and_card_type = raw_data.groupby(['age', 'card_type']).agg({'purchase_sum': 'sum'}).reset_index()
sum_purchases_by_age_and_card_type = sum_purchases_by_age_and_card_type.sort_values(by='purchase_sum', ascending=False)
fig15 = px.bar(sum_purchases_by_age_and_card_type, x='age', y='purchase_sum', color='card_type', title='Sum of purchases by age and card type')
st.plotly_chart(fig15, use_container_width=True)

# Count sum of purchases count by age and card type to new dataframe and render it as vertical bar chart
sum_purchases_count_by_age_and_card_type = raw_data.groupby(['age', 'card_type']).agg({'purchase_count': 'sum'}).reset_index()
sum_purchases_count_by_age_and_card_type = sum_purchases_count_by_age_and_card_type.sort_values(by='purchase_count', ascending=False)
fig16 = px.bar(sum_purchases_count_by_age_and_card_type, x='age', y='purchase_count', color='card_type', title='Sum of purchases count by age and card type')
st.plotly_chart(fig16, use_container_width=True)


# Count sum of purchases by city, age and card type to new dataframe and render it as 3d bar chart
sum_purchases_by_city_age_and_card_type = raw_data.groupby(['city', 'age', 'card_type']).agg({'purchase_sum': 'sum'}).reset_index()
sum_purchases_by_city_age_and_card_type = sum_purchases_by_city_age_and_card_type.sort_values(by='purchase_sum', ascending=False)

